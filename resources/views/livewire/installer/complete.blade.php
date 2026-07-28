<?php

use App\Domain\Installation\InstalledMarker;
use App\Models\WebsiteSetting;
use Livewire\Volt\Component;

new class extends Component {
    public WebsiteSetting $settings;
    public array $marker = [];

    public function mount(InstalledMarker $installedMarker): void
    {
        if (! session()->pull('installer.just_completed', false)) {
            $this->redirectRoute(auth()->check() ? 'dashboard' : 'login');
            return;
        }
        $this->settings = WebsiteSetting::query()->where('singleton_key', 1)->firstOrFail();
        $this->marker = $installedMarker->read() ?? [];
    }
}; ?>
<x-layouts.installer :current-step="9"><x-slot:title>Installation Complete</x-slot:title><div class="flex flex-col gap-7"><div class="flex size-16 items-center justify-center rounded-2xl bg-emerald-100 text-3xl text-emerald-700">✓</div><div><p class="text-sm font-semibold text-emerald-700">Step 9 of 9</p><h1 class="mt-2 text-3xl font-bold">Installation complete</h1><p class="mt-3 text-slate-600">{{ $settings->site_name }} is ready. The installer is now securely locked.</p></div><dl class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:grid-cols-3"><div><dt class="text-sm text-slate-500">Version</dt><dd class="font-semibold">{{ $marker['application_version'] ?? config('app.version') }}</dd></div><div><dt class="text-sm text-slate-500">License</dt><dd class="font-semibold text-emerald-700">Verified and active</dd></div><div><dt class="text-sm text-slate-500">Domain</dt><dd class="font-semibold">{{ $marker['normalized_domain'] ?? request()->getHost() }}</dd></div></dl><div class="flex flex-col gap-3 sm:flex-row"><flux:button href="{{ route('dashboard') }}" variant="primary">Admin Dashboard</flux:button><flux:button href="{{ route('home') }}">View Website</flux:button><flux:button href="{{ route('installer.license.diagnostics') }}">License Status</flux:button></div></div></x-layouts.installer>
