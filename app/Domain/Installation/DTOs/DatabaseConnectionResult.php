<?php

namespace App\Domain\Installation\DTOs;

final readonly class DatabaseConnectionResult
{
    private function __construct(public bool $successful, public string $message) {}

    public static function success(): self
    {
        return new self(true, 'The database connection was verified successfully.');
    }

    public static function failure(): self
    {
        return new self(false, 'We could not connect to the database. Check the details and try again.');
    }
}
