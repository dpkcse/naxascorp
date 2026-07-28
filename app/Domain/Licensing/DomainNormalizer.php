<?php

namespace App\Domain\Licensing;

use App\Domain\Licensing\Exceptions\LicenseException;
use Illuminate\Support\Str;

class DomainNormalizer
{
    public function normalize(string $value): string
    {
        $value = trim($value);
        if ($value === '' || preg_match('/[^\x20-\x7E]/', $value) === 1) {
            throw new LicenseException('The installation domain is invalid.', 'invalid_domain');
        }

        $candidate = str_contains($value, '://') ? $value : 'https://'.$value;
        $parts = parse_url($candidate);
        if ($parts === false || isset($parts['user'], $parts['pass']) || isset($parts['user'])) {
            throw new LicenseException('The installation domain is invalid.', 'invalid_domain');
        }
        if (! in_array(Str::lower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            throw new LicenseException('The installation domain uses an unsupported scheme.', 'invalid_domain');
        }

        $host = Str::lower(rtrim((string) ($parts['host'] ?? ''), '.'));
        $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
        $isHostname = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
        if ($host === '' || (! $isIp && ! $isHostname)) {
            throw new LicenseException('The installation domain is invalid.', 'invalid_domain');
        }

        return $host;
    }
}
