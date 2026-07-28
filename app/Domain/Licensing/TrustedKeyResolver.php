<?php

namespace App\Domain\Licensing;

use App\Domain\Licensing\Exceptions\LicenseException;

class TrustedKeyResolver
{
    public function resolve(string $keyId): string
    {
        $keys = (array) config('naxas-license.trusted_keys', []);
        $path = $keys[$keyId] ?? null;
        if ($path === null && count($keys) === 0 && $keyId === 'default') {
            $path = config('naxas-license.public_key_path');
        }
        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            throw new LicenseException('The trusted license verification key is unavailable.', 'unknown_key_id');
        }
        $pem = file_get_contents($path);
        if (! is_string($pem) || openssl_pkey_get_public($pem) === false) {
            throw new LicenseException('The trusted license verification key is invalid.', 'invalid_public_key');
        }

        return $pem;
    }

    public function isReadable(): bool
    {
        try {
            $keys = (array) config('naxas-license.trusted_keys', []);
            if ($keys !== []) {
                foreach (array_keys($keys) as $keyId) {
                    $this->resolve((string) $keyId);
                }
                return true;
            }
            $this->resolve('default');
            return true;
        } catch (LicenseException) {
            return false;
        }
    }
}
