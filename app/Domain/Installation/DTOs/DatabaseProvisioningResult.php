<?php

namespace App\Domain\Installation\DTOs;

final readonly class DatabaseProvisioningResult
{
    private function __construct(public bool $successful, public string $message) {}

    public static function success(): self
    {
        return new self(true, 'The database is ready for administrator setup.');
    }

    public static function failure(string $message = 'The database could not be prepared safely. No administrator was created.'): self
    {
        return new self(false, $message);
    }
}
