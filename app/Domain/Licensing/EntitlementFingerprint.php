<?php

namespace App\Domain\Licensing;

use App\Domain\Licensing\Exceptions\LicenseException;

class EntitlementFingerprint
{
    public function calculate(string $signedEntitlement): string
    {
        return hash('sha256', $signedEntitlement);
    }

    public function assertMatches(string $signedEntitlement, string $expected): string
    {
        if (preg_match('/\A[a-f0-9]{64}\z/', $expected) !== 1 || ! hash_equals($expected, $this->calculate($signedEntitlement))) {
            throw new LicenseException('The delivered license fingerprint could not be verified.', 'fingerprint_mismatch');
        }

        return $expected;
    }
}
