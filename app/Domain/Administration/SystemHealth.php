<?php

namespace App\Domain\Administration;

use App\Domain\Installation\InstalledMarker;
use App\Models\LicenseState;
use Illuminate\Support\Facades\DB;
use Throwable;

class SystemHealth
{
    /** @return array<int, array{label: string, status: string, message: string}> */
    public function checks(): array
    {
        return [
            $this->check('Installation', fn (): bool => app(InstalledMarker::class)->read() !== null, 'Installed marker is available.'),
            $this->check('Database', fn (): bool => DB::connection()->getPdo() !== null, 'Database connection is available.'),
            $this->check('Storage', fn (): bool => is_writable(storage_path()), 'Application storage is writable.'),
            $this->check('Public storage', fn (): bool => is_link(public_path('storage')), 'Public storage link is available.', 'warning'),
            $this->check('License state', fn (): bool => LicenseState::query()->whereNotNull('acknowledged_at')->exists(), 'Acknowledged license state is available.'),
            ['label' => 'Mail', 'status' => config('mail.default') === 'log' ? 'warning' : 'healthy', 'message' => config('mail.default') === 'log' ? 'Mail uses the log transport.' : 'Mail transport is configured.'],
            ['label' => 'Queue', 'status' => config('queue.default') === 'sync' ? 'warning' : 'healthy', 'message' => config('queue.default') === 'sync' ? 'Queue jobs run synchronously.' : 'A background queue is configured.'],
            ['label' => 'Scheduler', 'status' => 'warning', 'message' => 'Confirm the scheduler is configured on the host.'],
        ];
    }

    private function check(string $label, callable $check, string $failure = 'action-required'): array
    {
        try {
            return $check()
                ? ['label' => $label, 'status' => 'healthy', 'message' => 'Available and ready.']
                : ['label' => $label, 'status' => $failure, 'message' => 'Review this configuration.'];
        } catch (Throwable) {
            return ['label' => $label, 'status' => 'unavailable', 'message' => 'Status is temporarily unavailable.'];
        }
    }
}
