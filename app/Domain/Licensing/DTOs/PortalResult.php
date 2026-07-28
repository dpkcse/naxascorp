<?php

namespace App\Domain\Licensing\DTOs;

final readonly class PortalResult
{
    /** @param array<string, mixed> $data */
    public function __construct(public array $data, public ?string $correlationId = null) {}
}
