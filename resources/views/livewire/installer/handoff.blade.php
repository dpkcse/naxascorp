<?php

use Livewire\Volt\Component;

new class extends Component {}; ?>

<x-layouts.installer :current-step="5">
    <x-slot:title>Administrator created</x-slot:title>
    <div class="flex flex-col gap-6 text-center sm:items-center">
        <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><flux:icon name="check" class="size-7" /></span>
        <div class="flex flex-col gap-3">
            <p class="text-sm font-semibold text-emerald-700">Administrator created securely</p>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">License activation is the next step</h1>
            <p class="max-w-xl leading-6 text-slate-600">Phase 2 is complete. Naxora CMS is not yet marked as installed; Phase 3 will introduce the license activation step.</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">No license request has been made and no installation-complete marker exists.</div>
        <p class="text-xs text-slate-500">Developed and licensed by Naxas Innovations Limited</p>
    </div>
</x-layouts.installer>
