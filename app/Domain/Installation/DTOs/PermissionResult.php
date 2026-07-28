<?php

namespace App\Domain\Installation\DTOs;

final readonly class PermissionResult
{
    public function __construct(
        public string $path,
        public string $label,
        public bool $writable,
        public string $guidance,
    ) {}
}
