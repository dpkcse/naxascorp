<?php

use App\Domain\Licensing\EnvironmentResolver;
use App\Domain\Licensing\InstallationIdentity;
use App\Domain\Licensing\LicenseActivationService;
use App\Domain\Licensing\LicensePortalClient;
use App\Domain\Licensing\TrustedKeyResolver;
use App\Models\LicenseState;
use Livewire\Volt\Component;

new class extends Component {
    public array $diagnostics = [];

    public function mount(InstallationIdentity $identity, EnvironmentResolver $environment, LicenseActivationService $activation, LicensePortalClient $portal, TrustedKeyResolver $keys): void
    {
        $state = LicenseState::query()->where('product_slug', config('naxas-license.product'))->first();
        $this->diagnostics = [
            'Product' => config('naxas-license.product'), 'Version' => config('app.version'), 'Environment' => $environment->resolve(),
            'Normalized domain' => $activation->domain(request()->getHost()), 'Installation UUID' => $identity->get(),
            'Portal origin' => $portal->origin(), 'Public key readable' => $keys->isReadable() ? 'Yes' : 'No',
            'Status' => $state?->activation_status ?? 'not_requested', 'Request expiry' => $state?->request_expires_at?->toIso8601String() ?? '—',
            'Last verification' => $state?->last_verified_at?->toIso8601String() ?? '—', 'Acknowledged' => $state?->acknowledged_at?->toIso8601String() ?? '—',
            'Last safe failure code' => $state?->last_failure_code ?? '—',
        ];
    }
}; ?>
<x-layouts.installer :current-step="6"><x-slot:title>License Diagnostics</x-slot:title><div class="flex flex-col gap-6"><div><p class="text-sm font-semibold text-blue-700">Safe diagnostics</p><h1 class="mt-2 text-2xl font-bold">License Status</h1><p class="mt-2 text-slate-600">Secrets and entitlement payloads are intentionally excluded.</p></div><dl class="divide-y divide-slate-200 rounded-2xl border border-slate-200">@foreach ($diagnostics as $label => $value)<div class="grid gap-1 px-5 py-3 sm:grid-cols-2"><dt class="text-sm text-slate-500">{{ $label }}</dt><dd class="break-all text-sm font-semibold">{{ $value }}</dd></div>@endforeach</dl><flux:button href="{{ route('installer.license') }}">Back to License Activation</flux:button></div></x-layouts.installer>
