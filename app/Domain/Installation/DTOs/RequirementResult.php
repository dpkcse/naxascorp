<?php

namespace App\Domain\Installation\DTOs;

final readonly class RequirementResult
{
    public function __construct(
        public string $name,
        public string $label,
        public bool $passed,
        public bool $required = true,
        public string $detail = '',
    ) {}
}
