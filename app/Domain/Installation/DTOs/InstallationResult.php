<?php

namespace App\Domain\Installation\DTOs;

final readonly class InstallationResult
{
    public function __construct(public bool $successful, public string $message, public ?string $code = null) {}
}
