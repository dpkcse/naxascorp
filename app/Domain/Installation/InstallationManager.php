<?php

namespace App\Domain\Installation;

class InstallationManager
{
    public function __construct(private readonly InstallationState $state, private readonly AdministratorLifecycle $administratorLifecycle) {}

    public function isInstalled(): bool
    {
        return false;
    }

    public function canAccess(string $step): bool
    {
        if ($step === 'administrator_created' && $this->administratorLifecycle->hasAdministrator()) {
            return true;
        }

        return $this->state->previousStepIsComplete($step);
    }

    public function redirectRouteFor(string $step): string
    {
        return [
            'requirements_passed' => 'installer.welcome',
            'permissions_passed' => 'installer.requirements',
            'database_connection_verified' => 'installer.permissions',
            'administrator_created' => 'installer.database',
            'license_request_created' => 'installer.administrator',
            'license_entitlement_verified' => 'installer.license',
            'license_acknowledged' => 'installer.license',
        ][$step] ?? 'installer.welcome';
    }
}
