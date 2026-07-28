<?php

namespace App\Domain\Installation;

use Illuminate\Database\DatabaseManager;
use Throwable;

class InstallationManager
{
    public function __construct(private readonly InstallationState $state, private readonly AdministratorLifecycle $administratorLifecycle, private readonly InstalledState $installedState, private readonly DatabaseManager $database) {}

    public function isInstalled(): bool
    {
        return $this->installedState->isInstalled();
    }

    public function canAccess(string $step): bool
    {
        if ($step === 'administrator_created' && $this->administratorLifecycle->hasAdministrator()) {
            return true;
        }

        $previous = match ($step) {
            'website_settings_saved' => 'license_acknowledged',
            'demo_content_selected' => 'website_settings_saved',
            'installation_completed' => 'demo_content_selected',
            default => null,
        };
        if ($previous !== null) {
            try {
                return $this->database->table('installation_progress')->where('key', $previous)->exists();
            } catch (Throwable) {
                return false;
            }
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
            'website_settings_saved' => 'installer.license',
            'demo_content_selected' => 'installer.website',
            'installation_completed' => 'installer.demo-content',
        ][$step] ?? 'installer.welcome';
    }
}
