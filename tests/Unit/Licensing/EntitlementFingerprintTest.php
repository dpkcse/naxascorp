<?php

use App\Domain\Licensing\EntitlementFingerprint;
use App\Domain\Licensing\Exceptions\LicenseException;

it('uses the complete signed entitlement as the fingerprint source', function () {
    $token = 'payload.signature';
    $fingerprints = new EntitlementFingerprint;

    expect($fingerprints->calculate($token))->toBe(hash('sha256', $token))
        ->and($fingerprints->assertMatches($token, hash('sha256', $token)))->toBe(hash('sha256', $token));
});

it('rejects malformed and mismatched fingerprints', function (string $fingerprint) {
    expect(fn () => (new EntitlementFingerprint)->assertMatches('token', $fingerprint))->toThrow(LicenseException::class);
})->with(['ABC', str_repeat('A', 64), str_repeat('0', 64)]);
