<?php

namespace App\Domain\Installation;

use App\Domain\Installation\DTOs\InstallationResult;
use App\Models\InstallationChoice;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;

class DemoContentInstaller
{
    public function __construct(private readonly DatabaseManager $database) {}

    public function install(bool $selected): InstallationResult
    {
        $this->database->transaction(function () use ($selected): void {
            InstallationChoice::query()->updateOrCreate(['singleton_key' => 1], [
                'demo_content_selected' => $selected,
                'demo_content_status' => $selected ? 'deferred' : 'empty',
                'demo_content_completed_at' => now(),
            ]);
            $this->database->table('installation_progress')->updateOrInsert(['key' => 'demo_content_selected'], ['completed_at' => now()]);
        });
        Log::info($selected ? 'installation.demo_content_installed' : 'installation.demo_content_selected', ['selection' => $selected ? 'deferred_demo' : 'empty']);

        return new InstallationResult(true, $selected ? 'Demo content was selected and safely deferred until content modules are available.' : 'An empty website was selected.');
    }
}
