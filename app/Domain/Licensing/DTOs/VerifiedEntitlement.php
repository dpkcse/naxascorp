<?php

namespace App\Domain\Licensing\DTOs;

final readonly class VerifiedEntitlement
{
    /** @param array<string, mixed> $claims */
    public function __construct(public string $token, public string $fingerprint, public string $keyId, public array $claims) {}
}
