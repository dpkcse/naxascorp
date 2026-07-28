<?php

use App\Domain\Licensing\Exceptions\LicenseException;
use App\Domain\Licensing\SignedEntitlementVerifier;

beforeEach(function () {
    $keys = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
    openssl_pkey_export($keys, $privateKey);
    $publicKey = openssl_pkey_get_details($keys)['key'];
    $this->privateKey = $privateKey;
    $this->publicKeyPath = storage_path('framework/testing/license-public.pem');
    file_put_contents($this->publicKeyPath, $publicKey);
    config(['naxas-license.public_key_path' => $this->publicKeyPath, 'naxas-license.trusted_keys' => []]);
});

afterEach(function () {
    @unlink($this->publicKeyPath);
});

function signedLicenseToken(array $changes = [], ?string $privateKey = null): string
{
    $claims = array_merge([
        'key_id' => 'default', 'product' => 'naxora-cms', 'license_type' => 'single_site',
        'installation_uuid' => '550e8400-e29b-41d4-a716-446655440000', 'domain' => 'example.com',
        'status' => 'active', 'issued_at' => now()->subMinute()->toIso8601String(), 'expires_at' => now()->addYear()->toIso8601String(),
    ], $changes);
    $payload = rtrim(strtr(base64_encode(json_encode($claims, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
    openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

    return $payload.'.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
}

it('verifies the portal payload signature claims and complete-token fingerprint', function () {
    $token = signedLicenseToken(privateKey: $this->privateKey);
    $verified = app(SignedEntitlementVerifier::class)->verify($token, hash('sha256', $token), '550e8400-e29b-41d4-a716-446655440000', 'example.com');

    expect($verified->fingerprint)->toBe(hash('sha256', $token))->and($verified->claims['status'])->toBe('active');
});

it('rejects an invalid entitlement format', function () {
    expect(fn () => app(SignedEntitlementVerifier::class)->verify('not-a-token', hash('sha256', 'not-a-token'), '550e8400-e29b-41d4-a716-446655440000', 'example.com'))
        ->toThrow(LicenseException::class);
});

it('rejects an invalid rsa signature', function () {
    $token = signedLicenseToken(privateKey: $this->privateKey);
    $token .= 'changed';
    expect(fn () => app(SignedEntitlementVerifier::class)->verify($token, hash('sha256', $token), '550e8400-e29b-41d4-a716-446655440000', 'example.com'))
        ->toThrow(LicenseException::class);
});

it('rejects an unknown key identifier', function () {
    $token = signedLicenseToken(['key_id' => 'rotated'], $this->privateKey);
    expect(fn () => app(SignedEntitlementVerifier::class)->verify($token, hash('sha256', $token), '550e8400-e29b-41d4-a716-446655440000', 'example.com'))
        ->toThrow(LicenseException::class);
});

it('rejects invalid entitlement claims', function (array $changes) {
    $token = signedLicenseToken($changes, $this->privateKey);
    expect(fn () => app(SignedEntitlementVerifier::class)->verify($token, hash('sha256', $token), '550e8400-e29b-41d4-a716-446655440000', 'example.com'))
        ->toThrow(LicenseException::class);
})->with([
    'product' => [['product' => 'another-product']],
    'type' => [['license_type' => 'agency']],
    'uuid' => [['installation_uuid' => '660e8400-e29b-41d4-a716-446655440000']],
    'domain' => [['domain' => 'other.example.com']],
    'expired' => [['expires_at' => '2020-01-01T00:00:00+00:00']],
    'inactive' => [['status' => 'inactive']],
    'revoked' => [['status' => 'revoked']],
    'suspended' => [['status' => 'suspended']],
    'critical' => [['critical' => ['unknown']]],
]);
