<?php

namespace App\Domain\Installation;

use App\Domain\Installation\DTOs\InstallationResult;
use App\Models\InstallationChoice;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InstallationFinalizer
{
    public function __construct(private readonly DatabaseManager $database, private readonly EntitlementRevalidator $entitlements, private readonly InstalledMarker $marker) {}

    public function finalize(string $host): InstallationResult
    {
        Log::info('installation.finalization_started');
        try {
            $this->database->connection()->getPdo();
            foreach (['users', 'installation_progress', 'license_states', 'website_settings', 'installation_choices'] as $table) {
                if (! Schema::hasTable($table)) {
                    throw new \RuntimeException('A required database migration is missing.');
                }
            }
            $license = $this->entitlements->validate($host);
            if (! User::query()->where('is_active', true)->exists()) {
                throw new \RuntimeException('An active administrator is required.');
            }
            if (! WebsiteSetting::query()->where('singleton_key', 1)->exists()) {
                throw new \RuntimeException('Website Setup must be completed.');
            }
            if (! InstallationChoice::query()->where('singleton_key', 1)->exists()) {
                throw new \RuntimeException('Choose a demo-content option.');
            }

            if ($this->markerAgreesWith($license->installation_uuid, $license->normalized_domain) && $this->completed()) {
                return new InstallationResult(true, 'Installation is already complete.');
            }

            $installedAt = now();
            $this->database->transaction(function () use ($installedAt): void {
                $this->database->table('installation_progress')->updateOrInsert(['key' => 'installation_completed'], ['completed_at' => $installedAt]);
            });

            $written = $this->marker->write([
                'product' => (string) config('naxas-license.product'),
                'installation_uuid' => $license->installation_uuid,
                'installed_at' => $installedAt->toIso8601String(),
                'application_version' => (string) config('app.version'),
                'schema_version' => InstalledMarker::SchemaVersion,
                'normalized_domain' => $license->normalized_domain,
            ]);
            if (! $written) {
                $this->database->table('installation_progress')->where('key', 'installation_completed')->delete();
                throw new \RuntimeException('The installed marker could not be written. Check storage permissions and try again.');
            }
            Log::info('installation.finalization_completed', ['domain' => $license->normalized_domain]);

            return new InstallationResult(true, 'Naxora CMS was installed successfully.');
        } catch (Throwable $exception) {
            Log::warning('installation.finalization_failed', ['reason' => $exception->getMessage()]);
            return new InstallationResult(false, $exception->getMessage(), 'finalization_failed');
        }
    }

    private function completed(): bool
    {
        return $this->database->table('installation_progress')->where('key', 'installation_completed')->exists();
    }

    private function markerAgreesWith(string $uuid, string $domain): bool
    {
        $marker = $this->marker->read();
        return $marker !== null && hash_equals($uuid, $marker['installation_uuid']) && hash_equals($domain, $marker['normalized_domain']);
    }
}
