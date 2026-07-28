<?php

use App\Domain\Licensing\DomainNormalizer;
use App\Domain\Licensing\Exceptions\LicenseException;

it('normalizes domains without guessing away www', function (string $input, string $expected) {
    expect((new DomainNormalizer)->normalize($input))->toBe($expected);
})->with([
    ['HTTPS://WWW.Example.COM.:443/path?x=1#part', 'www.example.com'],
    ['example.com:8080/path', 'example.com'],
    ['127.0.0.1:8000', '127.0.0.1'],
    ['[::1]:8000', '::1'],
]);

it('rejects unsafe domains', function (string $input) {
    expect(fn () => (new DomainNormalizer)->normalize($input))->toThrow(LicenseException::class);
})->with(['ftp://example.com', 'https://user:pass@example.com', 'bad host', 'https://éxample.com']);
