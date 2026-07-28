<?php

namespace App\Domain\Installation;

class InstallationManager
{
    public function __construct(private readonly InstallationState $state) {}

    public function isInstalled(): bool
    {
        return false;
    }

    public function canAccess(string $step): bool
    {
        return $this->state->previousStepIsComplete($step);
    }

    public function redirectRouteFor(string $step): string
    {
        return [
            'requirements_passed' => 'installer.welcome',
            'permissions_passed' => 'installer.requirements',
            'database_connection_verified' => 'installer.permissions',
        ][$step] ?? 'installer.welcome';
    }
}
