<?php

use App\Domain\Installation\InstallationState;
use App\Domain\Installation\PermissionChecker;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public function getPermissionsProperty(): Collection
    {
        return collect(app(PermissionChecker::class)->check());
    }

    public function continue(PermissionChecker $checker, InstallationState $state): void
    {
        if (! $state->hasCompleted('requirements_passed')) {
            $this->redirectRoute('installer.requirements');
            return;
        }

        if (! $checker->passes()) {
            $state->forgetFrom('permissions_passed');
            $this->addError('permissions', 'Make the required paths writable, then check again.');
            return;
        }

        $state->markCompleted('permissions_passed');
        $state->forgetFrom('database_connection_verified');
        $this->redirectRoute('installer.database', navigate: true);
    }
}; ?>

<x-layouts.installer :current-step="3">
    <x-slot:title>File permissions</x-slot:title>
    <div class="flex flex-col gap-7">
        <div><p class="text-sm font-semibold text-blue-700">Step 3 of 7</p><h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Directory permissions</h1><p class="mt-3 leading-6 text-slate-600">Naxora only reports access here. It never changes operating-system permissions automatically.</p></div>
        @error('permissions') <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">{{ $message }}</div> @enderror
        <div class="flex flex-col gap-3">
            @foreach ($this->permissions as $permission)
                <div class="rounded-xl border p-4 {{ $permission->writable ? 'border-emerald-200 bg-emerald-50/60' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center justify-between gap-3"><code class="min-w-0 break-all text-sm font-semibold">{{ $permission->label }}</code><span class="shrink-0 text-xs font-bold {{ $permission->writable ? 'text-emerald-700' : 'text-red-700' }}">{{ $permission->writable ? 'Writable' : 'Action needed' }}</span></div>
                    @unless ($permission->writable)<p class="mt-2 text-sm leading-5 text-red-800">{{ $permission->guidance }}</p>@endunless
                </div>
            @endforeach
        </div>
        <div class="flex justify-end border-t border-slate-200 pt-6"><flux:button variant="primary" wire:click="continue" class="min-h-11 w-full justify-center sm:w-auto" icon-trailing="arrow-right">Continue</flux:button></div>
    </div>
</x-layouts.installer>
