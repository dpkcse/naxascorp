<?php

namespace App\Domain\Licensing\Exceptions;

use RuntimeException;

class LicenseException extends RuntimeException
{
    public function __construct(string $message, public readonly string $failureCode = 'license_error')
    {
        parent::__construct($message);
    }
}
