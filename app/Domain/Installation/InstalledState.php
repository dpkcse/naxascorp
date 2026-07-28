<?php

namespace App\Domain\Installation;

use App\Domain\Licensing\InstallationIdentity;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Database\DatabaseManager;
use Throwable;

class InstalledState
{
    public function __construct(private readonly DatabaseManager $database, private readonly InstalledMarker $marker, private readonly InstallationIdentity $identity, private readonly EntitlementRevalidator $entitlements) {}

    public function isInstalled(): bool
    {
        try {
            $marker = $this->marker->read();
            return $marker !== null
                && hash_equals($this->identity->get(), $marker['installation_uuid'])
                && $this->database->table('installation_progress')->where('key', 'installation_completed')->exists()
                && User::query()->where('is_active', true)->exists()
                && WebsiteSetting::query()->where('singleton_key', 1)->exists()
                && $this->entitlements->validate(request()->getHost()) !== null;
        } catch (Throwable) {
            return false;
        }
    }
}
