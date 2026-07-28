<?php

use App\Domain\Installation\DatabaseConnectionTester;
use App\Domain\Installation\DatabaseConfigurationActivator;
use App\Domain\Installation\DatabaseConfigurationStore;
use App\Domain\Installation\DatabaseProvisioner;
use App\Domain\Installation\InstallationState;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Component;

new class extends Component {
    public string $host = '127.0.0.1';
    public int|string $port = 3306;
    public string $database = '';
    public string $username = '';
    public string $password = '';
    public ?string $connectionMessage = null;
    public bool $connectionSuccessful = false;

    protected function rules(): array
    {
        return [
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'database' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_$-]+$/'],
            'username' => ['required', 'string', 'max:128'],
            'password' => ['nullable', 'string', 'max:1024'],
        ];
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['host', 'port', 'database', 'username', 'password'], true)) {
            app(InstallationState::class)->forgetFrom('database_connection_verified');
            $this->connectionSuccessful = false;
            $this->connectionMessage = null;
        }
    }

    public function testConnection(DatabaseConnectionTester $tester, InstallationState $state, DatabaseConfigurationStore $store, DatabaseConfigurationActivator $activator, DatabaseProvisioner $provisioner): void
    {
        if (! $state->hasCompleted('permissions_passed')) {
            $this->password = '';
            $this->redirectRoute('installer.permissions');
            return;
        }

        $state->forgetFrom('database_connection_verified');
        $key = 'installer-database:'.request()->ip().':'.session()->getId();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->password = '';
            $this->addError('connection', 'Too many attempts. Please wait before trying again.');
            return;
        }

        RateLimiter::hit($key, 60);

        try {
            $validated = $this->validate();
            $result = $tester->test([
                'host' => $validated['host'], 'port' => (int) $validated['port'],
                'database' => $validated['database'], 'username' => $validated['username'],
                'password' => $validated['password'] ?? '',
            ]);

            $this->connectionSuccessful = $result->successful;
            $this->connectionMessage = $result->message;

            if ($result->successful) {
                try {
                    $store->put([
                        'host' => $validated['host'], 'port' => (int) $validated['port'],
                        'database' => $validated['database'], 'username' => $validated['username'],
                        'password' => $validated['password'] ?? '',
                    ]);
                    $activator->activate();
                    $provisioning = $provisioner->prepare();
                    $this->connectionSuccessful = $provisioning->successful;
                    $this->connectionMessage = $provisioning->message;

                    if ($provisioning->successful) {
                        $state->markCompleted('database_connection_verified');
                        RateLimiter::clear($key);
                        $this->redirectRoute('installer.administrator', navigate: true);
                    }
                } catch (\Throwable) {
                    $this->connectionSuccessful = false;
                    $this->connectionMessage = 'The database configuration could not be secured. No changes were made to administrator setup.';
                }
            }
        } finally {
            $this->password = '';
        }
    }
}; ?>

<x-layouts.installer :current-step="4">
    <x-slot:title>Database connection</x-slot:title>
    <div class="flex flex-col gap-7">
        <div><p class="text-sm font-semibold text-blue-700">Step 4 of 5</p><h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Connect your database</h1><p class="mt-3 leading-6 text-slate-600">We first perform a harmless connection test. After verification, only pending Naxora migrations run; existing or unrecognized schemas are never overwritten.</p></div>

        @if ($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4"><p class="font-semibold text-red-900">Please review the highlighted fields.</p><ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-800">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if ($connectionMessage)<div role="status" class="rounded-xl border p-4 text-sm font-medium {{ $connectionSuccessful ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }}">{{ $connectionMessage }}</div>@endif

        <form wire:submit="testConnection" class="grid gap-5 sm:grid-cols-2" autocomplete="off">
            <flux:field class="sm:col-span-2"><flux:label>Database host</flux:label><flux:input wire:model.live.debounce.500ms="host" autocomplete="off" /><flux:error name="host" /></flux:field>
            <flux:field><flux:label>Database port</flux:label><flux:input type="number" min="1" max="65535" wire:model.live.debounce.500ms="port" inputmode="numeric" /><flux:error name="port" /></flux:field>
            <flux:field><flux:label>Database name</flux:label><flux:input wire:model.live.debounce.500ms="database" autocomplete="off" /><flux:error name="database" /></flux:field>
            <flux:field><flux:label>Database username</flux:label><flux:input wire:model.live.debounce.500ms="username" autocomplete="off" /><flux:error name="username" /></flux:field>
            <flux:field><flux:label>Database password <span class="font-normal text-slate-500">(optional)</span></flux:label><flux:input type="password" wire:model="password" autocomplete="new-password" /><flux:error name="password" /></flux:field>
            <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:col-span-2 sm:flex-row sm:items-center sm:justify-between"><p class="text-xs leading-5 text-slate-500">Credentials are encrypted in server-only installer storage and never placed in the session or browser.</p><flux:button type="submit" variant="primary" wire:loading.attr="disabled" class="min-h-11 w-full justify-center sm:w-auto" icon="circle-stack">Test Database Connection</flux:button></div>
        </form>
    </div>
</x-layouts.installer>
