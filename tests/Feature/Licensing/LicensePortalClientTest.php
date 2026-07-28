<?php

use App\Domain\Licensing\Exceptions\LicenseException;
use App\Domain\Licensing\LicensePortalClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    config([
        'naxas-license.server_url' => 'https://license.example.com',
        'naxas-license.product' => 'naxora-cms',
        'naxas-license.license_type' => 'single_site',
        'naxas-license.max_response_bytes' => 131072,
    ]);
});

it('sends the authoritative activation request payload without retrying', function () {
    Http::fake(['https://license.example.com/api/v1/activation-requests' => Http::response([
        'request_id' => (string) Str::uuid(), 'request_token' => 'BRQ-secret', 'status' => 'pending',
    ])]);
    $payload = [
        'product' => 'naxora-cms', 'version' => '1.0.0', 'license_type' => 'single_site',
        'installation_uuid' => (string) Str::uuid(), 'domain' => 'example.com', 'environment' => 'testing', 'nonce' => Str::random(64),
    ];

    app(LicensePortalClient::class)->createActivationRequest($payload);

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://license.example.com/api/v1/activation-requests'
        && $request->hasHeader('Accept', 'application/json')
        && $request->data() === $payload
        && strlen($request['nonce']) >= 16 && strlen($request['nonce']) <= 128);
});

it('rejects insecure production transport', function () {
    config(['app.env' => 'production', 'naxas-license.server_url' => 'http://license.example.com']);
    expect(fn () => app(LicensePortalClient::class)->origin())->toThrow(LicenseException::class);
});

it('allows local http only for an explicitly trusted host', function () {
    app()->detectEnvironment(fn () => 'local');
    config(['naxas-license.server_url' => 'http://127.0.0.1:8001', 'naxas-license.allow_local_http' => true, 'naxas-license.trusted_local_hosts' => ['127.0.0.1']]);
    expect(app(LicensePortalClient::class)->origin())->toBe('http://127.0.0.1:8001');

    config(['naxas-license.server_url' => 'http://192.168.1.2:8001']);
    expect(fn () => app(LicensePortalClient::class)->origin())->toThrow(LicenseException::class);
});

it('rejects malformed oversized and redirected responses', function (array $response, string $failureCode) {
    Http::fake(['*' => Http::response($response['body'], $response['status'], $response['headers'] ?? [])]);
    config(['naxas-license.max_response_bytes' => $response['limit'] ?? 131072]);
    try {
        app(LicensePortalClient::class)->createActivationRequest([]);
    } catch (LicenseException $exception) {
        expect($exception->failureCode)->toBe($failureCode);
        return;
    }
    $this->fail('Expected a safe portal exception.');
})->with([
    'malformed json' => [['body' => '{bad', 'status' => 200], 'malformed_json'],
    'oversized' => [['body' => '123456', 'status' => 200, 'limit' => 5], 'response_too_large'],
    'redirect' => [['body' => '', 'status' => 302, 'headers' => ['Location' => 'https://elsewhere.example']], 'unexpected_redirect'],
]);
