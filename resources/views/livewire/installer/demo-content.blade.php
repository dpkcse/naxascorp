<?php

use App\Domain\Installation\DemoContentInstaller;
use App\Domain\Installation\EntitlementRevalidator;
use App\Domain\Installation\InstallationFinalizer;
use App\Domain\Installation\InstalledState;
use App\Models\WebsiteSetting;
use Livewire\Volt\Component;

new class extends Component {
    public ?string $choice = null;

    public function mount(EntitlementRevalidator $entitlements, InstalledState $installed): void
    {
        if ($installed->isInstalled()) { $this->redirectRoute('dashboard'); return; }
        if (! WebsiteSetting::query()->where('singleton_key', 1)->exists()) {
            $this->redirectRoute('installer.website');
            return;
        }
        try { $entitlements->validate(request()->getHost()); } catch (Throwable) { $this->redirectRoute('installer.license'); }
    }

    public function continue(DemoContentInstaller $demo, InstallationFinalizer $finalizer, InstalledState $installed): void
    {
        if ($installed->isInstalled()) { $this->redirectRoute('dashboard'); return; }
        $this->validate(['choice' => ['required', 'in:demo,empty']]);
        $demo->install($this->choice === 'demo');
        $result = $finalizer->finalize(request()->getHost());
        if (! $result->successful) {
            $this->addError('finalization', $result->message);
            return;
        }
        session()->forget('installer.progress');
        session()->put('installer.just_completed', true);
        $this->redirectRoute('installer.complete');
    }
}; ?>
<x-layouts.installer :current-step="8"><x-slot:title>Demo Content Choice</x-slot:title><div class="flex flex-col gap-7"><div><p class="text-sm font-semibold text-blue-700">Step 8 of 9</p><h1 class="mt-2 text-3xl font-bold">Choose your starting point</h1><p class="mt-2 text-slate-600">Make an explicit, reversible choice. No external downloads or destructive seeders are used.</p></div>@error('finalization')<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-900">{{ $message }}</div>@enderror<form wire:submit="continue" class="flex flex-col gap-5"><fieldset class="grid gap-4 md:grid-cols-2"><legend class="sr-only">Demo content choice</legend><label class="cursor-pointer rounded-2xl border border-slate-200 p-5 has-checked:border-blue-600 has-checked:ring-2 has-checked:ring-blue-100"><input wire:model="choice" type="radio" value="demo" class="mr-2"> <strong>Professional corporate demo</strong><p class="mt-3 text-sm text-slate-600">Records a deferred premium demo choice. Content modules and sample administrators are not created.</p></label><label class="cursor-pointer rounded-2xl border border-slate-200 p-5 has-checked:border-blue-600 has-checked:ring-2 has-checked:ring-blue-100"><input wire:model="choice" type="radio" value="empty" class="mr-2"> <strong>Start with an empty website</strong><p class="mt-3 text-sm text-slate-600">Keeps your website settings and adds no sample content.</p></label></fieldset>@error('choice')<p class="text-sm text-red-700">{{ $message }}</p>@enderror<div><flux:button type="submit" variant="primary">Finalize Installation</flux:button></div></form></div></x-layouts.installer>
