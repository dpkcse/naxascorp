<?php

namespace App\Domain\Installation\DTOs;

use App\Models\User;

final readonly class InitialAdministratorResult
{
    private function __construct(public bool $successful, public ?User $administrator, public string $message) {}

    public static function success(User $administrator): self
    {
        return new self(true, $administrator, 'Administrator created securely.');
    }

    public static function conflict(): self
    {
        return new self(false, null, 'Administrator setup is already configured or conflicts with existing application data.');
    }
}
