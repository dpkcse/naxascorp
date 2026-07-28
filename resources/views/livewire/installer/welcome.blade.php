<?php

use App\Domain\Installation\InstallationState;
use Livewire\Volt\Component;

new class extends Component {
    public function start(InstallationState $state): void
    {
        $state->markCompleted('welcome_completed');
        $state->forgetFrom('requirements_passed');
        $this->redirectRoute('installer.requirements', navigate: true);
    }
}; ?>

<x-layouts.installer :current-step="1">
    <x-slot:title>Welcome</x-slot:title>

    <div class="flex flex-col gap-8">
        <div class="flex flex-col gap-4">
            <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold tracking-wide text-blue-700 uppercase">Premium Corporate Website CMS</span>
            <div class="flex flex-col gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Welcome to Naxora CMS</h1>
                <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">This guided setup will check your server, confirm safe file access, and verify your MySQL database connection. It will not create an administrator or modify your database in this phase.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([['shield-check', 'Secure by design', 'Checks and progression are enforced on the server.'], ['server-stack', 'Hosting ready', 'Clear checks help identify hosting requirements.'], ['building-office-2', 'Enterprise focused', 'A clean foundation for corporate websites.']] as [$icon, $title, $copy])
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <flux:icon :name="$icon" class="size-5 text-blue-600" />
                    <p class="mt-3 text-sm font-semibold">{{ $title }}</p>
                    <p class="mt-1 text-sm leading-5 text-slate-600">{{ $copy }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">Application version {{ config('app.version', '1.0.0') }}</p>
            <flux:button variant="primary" wire:click="start" wire:loading.attr="disabled" class="min-h-11 w-full justify-center sm:w-auto" icon-trailing="arrow-right">Start Installation</flux:button>
        </div>
    </div>
</x-layouts.installer>
