<?php

namespace App\Domain\Installation;

use App\Domain\Installation\DTOs\RequirementResult;

class RequirementChecker
{
    /** @return list<RequirementResult> */
    public function check(): array
    {
        $extensions = [
            ['openssl', 'OpenSSL', true], ['pdo', 'PDO', true], ['pdo_mysql', 'PDO MySQL', true],
            ['mbstring', 'Mbstring', true], ['tokenizer', 'Tokenizer', true], ['xml', 'XML', true],
            ['ctype', 'Ctype', true], ['json', 'JSON', true], ['fileinfo', 'Fileinfo', true],
            ['bcmath', 'BCMath', true], ['curl', 'cURL', true], ['zip', 'ZIP', true],
            ['intl', 'Intl', false], ['Zend OPcache', 'OPcache', false],
        ];

        $results = [new RequirementResult('php', 'PHP 8.2 or newer', version_compare(PHP_VERSION, '8.2.0', '>='), true, PHP_VERSION)];

        foreach ($extensions as [$extension, $label, $required]) {
            $results[] = new RequirementResult($extension, $label, extension_loaded($extension), $required);
        }

        $imageSupport = extension_loaded('gd') || extension_loaded('imagick');
        $results[] = new RequirementResult('image', 'GD or Imagick', $imageSupport, true);

        return $results;
    }

    public function passes(): bool
    {
        foreach ($this->check() as $result) {
            if ($result->required && ! $result->passed) {
                return false;
            }
        }

        return true;
    }
}
