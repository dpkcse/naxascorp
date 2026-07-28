<?php

namespace App\Domain\Licensing;

use App\Domain\Licensing\DTOs\VerifiedEntitlement;
use App\Domain\Licensing\Exceptions\LicenseException;
use DateTimeImmutable;
use Throwable;

class SignedEntitlementVerifier
{
    public function __construct(private readonly TrustedKeyResolver $keys, private readonly EntitlementFingerprint $fingerprints) {}

    public function verify(string $token, string $expectedFingerprint, string $installationUuid, string $domain): VerifiedEntitlement
    {
        if (strlen($token) > (int) config('naxas-license.max_response_bytes')) {
            throw new LicenseException('The signed license is too large.', 'entitlement_too_large');
        }
        $segments = explode('.', $token);
        if (count($segments) !== 2 || $segments[0] === '' || $segments[1] === '') {
            throw new LicenseException('The signed license format is invalid.', 'invalid_entitlement_format');
        }
        $payloadJson = $this->decode($segments[0]);
        $signature = $this->decode($segments[1]);
        try {
            $claims = json_decode($payloadJson, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw new LicenseException('The signed license payload is invalid.', 'invalid_entitlement_payload');
        }
        if (! is_array($claims)) {
            throw new LicenseException('The signed license payload is invalid.', 'invalid_entitlement_payload');
        }
        $keyId = $claims['key_id'] ?? null;
        if (! is_string($keyId) || $keyId === '') {
            throw new LicenseException('The signed license key identifier is invalid.', 'unknown_key_id');
        }
        if (openssl_verify($segments[0], $signature, $this->keys->resolve($keyId), OPENSSL_ALGO_SHA256) !== 1) {
            throw new LicenseException('The signed license signature is invalid.', 'invalid_signature');
        }
        $fingerprint = $this->fingerprints->assertMatches($token, $expectedFingerprint);
        $this->validateClaims($claims, $installationUuid, $domain);

        return new VerifiedEntitlement($token, $fingerprint, $keyId, $claims);
    }

    /** @param array<string, mixed> $claims */
    private function validateClaims(array $claims, string $installationUuid, string $domain): void
    {
        $expected = [
            'product' => (string) config('naxas-license.product'),
            'license_type' => (string) config('naxas-license.license_type'),
            'installation_uuid' => $installationUuid,
            'domain' => $domain,
            'status' => 'active',
        ];
        foreach ($expected as $claim => $value) {
            if (! isset($claims[$claim]) || ! is_string($claims[$claim]) || ! hash_equals($value, $claims[$claim])) {
                throw new LicenseException("The signed license {$claim} claim is invalid.", 'claim_'.$claim);
            }
        }
        if (isset($claims['critical']) && $claims['critical'] !== []) {
            throw new LicenseException('The signed license contains unsupported critical claims.', 'unsupported_critical_claim');
        }
        $issuedAt = $this->timestamp($claims['issued_at'] ?? null, 'issued_at');
        if ($issuedAt > new DateTimeImmutable('+5 minutes')) {
            throw new LicenseException('The signed license issue time is invalid.', 'claim_issued_at');
        }
        if (isset($claims['expires_at']) && $claims['expires_at'] !== null && $this->timestamp($claims['expires_at'], 'expires_at') <= new DateTimeImmutable()) {
            throw new LicenseException('The signed license has expired.', 'claim_expires_at');
        }
        foreach (['support_expires_at', 'updates_expires_at'] as $optionalDate) {
            if (isset($claims[$optionalDate]) && $claims[$optionalDate] !== null) {
                $this->timestamp($claims[$optionalDate], $optionalDate);
            }
        }
    }

    private function timestamp(mixed $value, string $claim): DateTimeImmutable
    {
        try {
            if (! is_string($value) || $value === '') {
                throw new \RuntimeException();
            }
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            throw new LicenseException("The signed license {$claim} claim is invalid.", 'claim_'.$claim);
        }
    }

    private function decode(string $value): string
    {
        if (preg_match('/\A[A-Za-z0-9_-]+\z/', $value) !== 1) {
            throw new LicenseException('The signed license encoding is invalid.', 'invalid_entitlement_format');
        }
        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);
        if ($decoded === false) {
            throw new LicenseException('The signed license encoding is invalid.', 'invalid_entitlement_format');
        }
        return $decoded;
    }
}
