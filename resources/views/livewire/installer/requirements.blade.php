<?php

use App\Domain\Installation\InstallationState;
use App\Domain\Installation\RequirementChecker;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public function getRequirementsProperty(): Collection
    {
        return collect(app(RequirementChecker::class)->check());
    }

    public function continue(RequirementChecker $checker, InstallationState $state): void
    {
        if (! $state->hasCompleted('welcome_completed')) {
            $this->redirectRoute('installer.welcome');
            return;
        }

        if (! $checker->passes()) {
            $state->forgetFrom('requirements_passed');
            $this->addError('requirements', 'Resolve the required server checks before continuing.');
            return;
        }

        $state->markCompleted('requirements_passed');
        $state->forgetFrom('permissions_passed');
        $this->redirectRoute('installer.permissions', navigate: true);
    }
}; ?>

<x-layouts.installer :current-step="2">
    <x-slot:title>Server requirements</x-slot:title>
    <div class="flex flex-col gap-7">
        <div><p class="text-sm font-semibold text-blue-700">Step 2 of 4</p><h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Server requirements</h1><p class="mt-3 leading-6 text-slate-600">Required items must pass. Recommended items improve performance but do not block installation.</p></div>
        @error('requirements') <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800">{{ $message }}</div> @enderror
        <div class="overflow-hidden rounded-xl border border-slate-200">
            @foreach ($this->requirements as $requirement)
                <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-3 last:border-0 sm:px-5">
                    <div class="min-w-0"><p class="font-medium">{{ $requirement->label }}</p><p class="text-xs text-slate-500">{{ $requirement->required ? 'Required' : 'Recommended' }}{{ $requirement->detail ? ' · '.$requirement->detail : '' }}</p></div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $requirement->passed ? 'bg-emerald-100 text-emerald-800' : ($requirement->required ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $requirement->passed ? 'Passed' : ($requirement->required ? 'Required' : 'Optional') }}</span>
                </div>
            @endforeach
        </div>
        <div class="flex justify-end border-t border-slate-200 pt-6"><flux:button variant="primary" wire:click="continue" class="min-h-11 w-full justify-center sm:w-auto" icon-trailing="arrow-right">Continue</flux:button></div>
    </div>
</x-layouts.installer>
