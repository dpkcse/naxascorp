<?php

use App\Domain\Licensing\EnvironmentResolver;
use App\Domain\Licensing\Exceptions\LicenseException;
use App\Domain\Licensing\InstallationIdentity;
use App\Domain\Licensing\LicenseActivationService;
use App\Models\LicenseState;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Component;

new class extends Component {
    public ?LicenseState $licenseState = null;
    public string $installationUuid = '';
    public string $normalizedDomain = '';
    public string $environment = '';
    public ?string $requestToken = null;

    public function mount(InstallationIdentity $identity, EnvironmentResolver $environment, LicenseActivationService $activation): void
    {
        $this->installationUuid = $identity->get();
        $this->environment = $environment->resolve();
        $this->normalizedDomain = $activation->domain(request()->getHost());
        $this->licenseState = LicenseState::query()->where('product_slug', config('naxas-license.product'))->first();
        $this->loadDisplayToken();
    }

    public function createRequest(LicenseActivationService $activation): void
    {
        $this->runRateLimited('create', fn () => $this->licenseState = $activation->createRequest(request()->getHost()));
        $this->loadDisplayToken();
    }

    public function checkStatus(LicenseActivationService $activation): void
    {
        $this->runRateLimited('status', fn () => $this->licenseState = $activation->checkStatus(request()->getHost()));
        $this->requestToken = null;
    }

    private function runRateLimited(string $action, callable $callback): void
    {
        $key = 'installer-license:'.$action.':'.request()->ip().':'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->addError('license', 'Too many activation attempts. Please wait before trying again.');
            return;
        }
        RateLimiter::hit($key, 60);
        try {
            $callback();
        } catch (LicenseException $exception) {
            $this->addError('license', $exception->getMessage());
        }
    }

    private function loadDisplayToken(): void
    {
        $this->requestToken = $this->licenseState?->acknowledged_at === null ? $this->licenseState?->encrypted_request_token : null;
    }
}; ?>

<x-layouts.installer :current-step="6">
    <x-slot:title>License Activation</x-slot:title>
    <div class="flex flex-col gap-7">
        <div class="flex flex-col gap-3">
            <p class="text-sm font-semibold text-blue-700">Step 6 of 7</p>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">License Activation</h1>
            <p class="max-w-2xl leading-6 text-slate-600">Connect Naxora CMS to the Naxas License Portal for administrator approval.</p>
        </div>
        @if ($errors->has('license'))<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900">{{ $errors->first('license') }}</div>@endif
        @if ($licenseState?->last_safe_message)<div role="status" class="rounded-xl border {{ $licenseState->acknowledged_at ? 'border-emerald-200 bg-emerald-50 text-emerald-950' : 'border-amber-200 bg-amber-50 text-amber-950' }} p-4 text-sm">{{ $licenseState->last_safe_message }}</div>@endif
        <dl class="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Product</dt><dd class="font-semibold">Naxora CMS · {{ config('naxas-license.product') }}</dd></div>
            <div><dt class="text-slate-500">Version / license</dt><dd class="font-semibold">{{ config('app.version') }} · {{ config('naxas-license.license_type') }}</dd></div>
            <div><dt class="text-slate-500">Installation UUID</dt><dd class="break-all font-mono text-xs">{{ $installationUuid }}</dd></div>
            <div><dt class="text-slate-500">Domain / environment</dt><dd class="font-semibold">{{ $normalizedDomain }} · {{ $environment }}</dd></div>
            <div><dt class="text-slate-500">Activation status</dt><dd class="font-semibold capitalize">{{ str_replace('_', ' ', $licenseState?->activation_status ?? 'not requested') }}</dd></div>
            <div><dt class="text-slate-500">Request expiry</dt><dd class="font-semibold">{{ $licenseState?->request_expires_at?->toDayDateTimeString() ?? '—' }}</dd></div>
        </dl>
        @if ($requestToken)
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5" x-data="{ copied: false }">
                <p class="text-sm font-semibold text-blue-950">Activation request token</p>
                <code class="mt-2 block break-all rounded-lg bg-white p-3 text-sm text-slate-900">{{ $requestToken }}</code>
                <button type="button" class="mt-3 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white outline-none focus-visible:ring-2 focus-visible:ring-blue-600" x-on:click="navigator.clipboard.writeText($el.previousElementSibling.textContent.trim()); copied = true" x-text="copied ? 'Copied' : 'Copy Request Token'">Copy Request Token</button>
            </div>
        @endif
        <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:flex-wrap">
            @if (! $licenseState?->request_id || in_array($licenseState?->activation_status, ['rejected', 'expired', 'delivery_expired'], true))
                <flux:button wire:click="createRequest" variant="primary" wire:loading.attr="disabled">{{ $licenseState?->request_id ? 'Generate New Request' : 'Generate Activation Request' }}</flux:button>
            @endif
            @if ($licenseState?->portal_url && ! $licenseState?->acknowledged_at)<flux:button href="{{ $licenseState->portal_url }}" target="_blank" rel="noopener noreferrer">Open Activation Portal</flux:button>@endif
            @if ($licenseState?->request_id && ! $licenseState?->acknowledged_at)<flux:button wire:click="checkStatus" wire:loading.attr="disabled">Check Activation Status</flux:button>@endif
            @if ($licenseState?->acknowledged_at)<flux:button href="{{ route('installer.ready') }}" variant="primary">Continue to Installation Ready</flux:button>@endif
            <flux:button href="{{ route('installer.license.diagnostics') }}" variant="ghost">Diagnostics</flux:button>
        </div>
        <p class="text-xs text-slate-500">Developed and licensed by Naxas Innovations Limited. The signed entitlement is never displayed.</p>
    </div>
</x-layouts.installer>
