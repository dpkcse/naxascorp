<?php

use App\Models\LicenseState;
use Livewire\Volt\Component;

new class extends Component {
    public function mount(): void
    {
        if (! LicenseState::query()->where('product_slug', config('naxas-license.product'))->whereNotNull('acknowledged_at')->exists()) {
            $this->redirectRoute('installer.license');
        }
    }
}; ?>
<x-layouts.installer :current-step="7"><x-slot:title>Installation Ready</x-slot:title><div class="flex flex-col gap-6"><div class="flex size-14 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-700">✓</div><div><p class="text-sm font-semibold text-emerald-700">Step 7 of 7</p><h1 class="mt-2 text-3xl font-bold">License activated</h1><p class="mt-3 max-w-2xl text-slate-600">Naxora CMS is ready for Phase 4 installation completion. No demo content or final installed marker has been created.</p></div><div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-950">Keep the application key and installation identity file backed up securely. They are required to read and validate the local entitlement.</div></div></x-layouts.installer>
