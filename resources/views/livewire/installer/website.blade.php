<?php

use App\Domain\Installation\EntitlementRevalidator;
use App\Domain\Installation\InstalledState;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $site_name = '';
    public string $legal_name = '';
    public string $tagline = '';
    public string $primary_email = '';
    public string $primary_phone = '';
    public string $country_code = 'BD';
    public string $timezone = 'Asia/Dhaka';
    public string $default_locale = 'en';
    public string $site_url = '';

    public function mount(EntitlementRevalidator $entitlements, InstalledState $installed): void
    {
        if ($installed->isInstalled()) {
            $this->redirectRoute('dashboard');
            return;
        }
        try {
            $entitlements->validate(request()->getHost());
        } catch (Throwable) {
            $this->redirectRoute('installer.license');
            return;
        }
        $settings = WebsiteSetting::query()->where('singleton_key', 1)->first();
        if ($settings) {
            foreach ($settings->only(['site_name', 'legal_name', 'tagline', 'primary_email', 'primary_phone', 'country_code', 'timezone', 'default_locale', 'site_url']) as $key => $value) {
                $this->{$key} = (string) $value;
            }
        } else {
            $this->site_url = url('/');
            $this->primary_email = (string) auth()->user()?->email;
        }
    }

    public function save(EntitlementRevalidator $entitlements, InstalledState $installed): void
    {
        if ($installed->isInstalled()) {
            $this->redirectRoute('dashboard');
            return;
        }
        try {
            $license = $entitlements->validate(request()->getHost());
        } catch (Throwable) {
            $this->redirectRoute('installer.license');
            return;
        }
        foreach (['site_name', 'legal_name', 'tagline', 'primary_email', 'primary_phone', 'country_code', 'timezone', 'default_locale', 'site_url'] as $field) {
            $this->{$field} = trim($this->{$field});
        }
        $this->primary_email = strtolower($this->primary_email);
        $this->country_code = strtoupper($this->country_code);
        $this->site_url = rtrim($this->site_url, '/');
        $this->validate([
            'site_name' => ['required', 'string', 'min:2', 'max:120'],
            'legal_name' => ['required', 'string', 'min:2', 'max:180'],
            'tagline' => ['nullable', 'string', 'max:240'],
            'primary_email' => ['required', 'email:rfc', 'max:254'],
            'primary_phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]*$/'],
            'country_code' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'timezone' => ['required', Rule::timezone()],
            'default_locale' => ['required', Rule::in(['en'])],
            'site_url' => ['required', 'url:http,https', 'max:2048'],
        ]);
        $parts = parse_url($this->site_url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $local = app()->environment(['local', 'testing']) || in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if (! $local && $scheme !== 'https') {
            $this->addError('site_url', 'HTTPS is required for a production website.');
            return;
        }
        if (! hash_equals($license->normalized_domain, app(\App\Domain\Licensing\DomainNormalizer::class)->normalize($host))) {
            $this->addError('site_url', 'The website URL must match the activated license domain.');
            return;
        }
        DB::transaction(function (): void {
            WebsiteSetting::query()->updateOrCreate(['singleton_key' => 1], $this->only(['site_name', 'legal_name', 'tagline', 'primary_email', 'primary_phone', 'country_code', 'timezone', 'default_locale', 'site_url']));
            DB::table('installation_progress')->updateOrInsert(['key' => 'website_settings_saved'], ['completed_at' => now()]);
        });
        Log::info('installation.website_settings_saved');
        $this->redirectRoute('installer.demo-content', navigate: true);
    }
}; ?>

<x-layouts.installer :current-step="7"><x-slot:title>Website Setup</x-slot:title>
<div class="flex flex-col gap-7"><div><p class="text-sm font-semibold text-blue-700">Step 7 of 9</p><h1 class="mt-2 text-3xl font-bold tracking-tight">Website Setup</h1><p class="mt-2 text-slate-600">Add the essential organization, contact and regional details.</p></div>
<form wire:submit="save" class="flex flex-col gap-7">
<section class="grid gap-4 rounded-2xl border border-slate-200 p-5 sm:grid-cols-2"><h2 class="text-lg font-semibold sm:col-span-2">Organization</h2><flux:input wire:model="site_name" label="Website name" required /><flux:input wire:model="legal_name" label="Legal organization name" required /><flux:input wire:model="tagline" label="Short tagline (optional)" class="sm:col-span-2" /></section>
<section class="grid gap-4 rounded-2xl border border-slate-200 p-5 sm:grid-cols-2"><h2 class="text-lg font-semibold sm:col-span-2">Contact</h2><flux:input wire:model="primary_email" type="email" label="Primary email" required /><flux:input wire:model="primary_phone" type="tel" label="Primary phone (optional)" /></section>
<section class="grid gap-4 rounded-2xl border border-slate-200 p-5 sm:grid-cols-3"><h2 class="text-lg font-semibold sm:col-span-3">Regional settings</h2><flux:input wire:model="country_code" label="Country code" maxlength="2" required /><flux:select wire:model="timezone" label="Timezone"><flux:select.option value="Asia/Dhaka">Asia/Dhaka</flux:select.option><flux:select.option value="UTC">UTC</flux:select.option></flux:select><flux:select wire:model="default_locale" label="Default locale"><flux:select.option value="en">English</flux:select.option></flux:select></section>
<section class="rounded-2xl border border-blue-200 bg-blue-50 p-5"><h2 class="font-semibold text-blue-950">URL and licensed domain</h2><flux:input wire:model="site_url" type="url" label="Website URL" required class="mt-4" /><p class="mt-2 text-sm text-blue-900">This URL must use the domain verified during license activation.</p></section>
<div><flux:button type="submit" variant="primary">Save and Continue</flux:button></div></form></div></x-layouts.installer>
