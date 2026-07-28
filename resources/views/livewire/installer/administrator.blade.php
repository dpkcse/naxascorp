<?php

use App\Domain\Installation\AdministratorLifecycle;
use App\Domain\Installation\InitialAdministratorCreator;
use App\Domain\Installation\InstallationState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(AdministratorLifecycle $lifecycle, InstallationState $state): void
    {
        if ($lifecycle->hasAdministrator()) {
            $state->markCompleted('administrator_created');
            $this->redirectRoute('installer.handoff');
        }
    }

    public function createAdministrator(InitialAdministratorCreator $creator, InstallationState $state): void
    {
        $key = 'installer-administrator:'.request()->ip().':'.session()->getId();

        try {
            if (! $state->hasCompleted('database_connection_verified')) {
                $this->redirectRoute('installer.database');

                return;
            }

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $this->addError('administrator', 'Too many attempts. Please wait before trying again.');

                return;
            }

            RateLimiter::hit($key, 60);
            $this->name = Str::squish($this->name);
            $this->email = Str::lower(trim($this->email));
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            ]);

            $result = $creator->create($validated);
            if (! $result->successful || $result->administrator === null) {
                $this->addError('administrator', $result->message);

                return;
            }

            $state->markCompleted('administrator_created');
            RateLimiter::clear($key);
            Auth::login($result->administrator);
            session()->regenerate();
            $this->redirectRoute('installer.handoff', navigate: true);
        } finally {
            $this->reset('password', 'password_confirmation');
        }
    }
}; ?>

<x-layouts.installer :current-step="5">
    <x-slot:title>Administrator Account</x-slot:title>

    <div class="flex flex-col gap-7">
        <div class="flex flex-col gap-3">
            <p class="text-sm font-semibold text-blue-700">Step 5 of 5</p>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Administrator Account</h1>
            <p class="max-w-2xl leading-6 text-slate-600">Create the single account used to manage Naxora CMS. Choose a unique passphrase and store it in an approved password manager.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="font-semibold text-red-900">Administrator setup could not continue.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-800">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form wire:submit="createAdministrator" class="flex flex-col gap-5" autocomplete="off">
            <flux:field><flux:label>Full name</flux:label><flux:input wire:model="name" name="name" autocomplete="name" required autofocus /><flux:error name="name" /></flux:field>
            <flux:field><flux:label>Email address</flux:label><flux:input wire:model="email" type="email" name="email" autocomplete="email" required /><flux:error name="email" /></flux:field>
            <div class="grid gap-5 sm:grid-cols-2">
                <flux:field><flux:label>Password</flux:label><flux:input wire:model="password" type="password" name="password" autocomplete="new-password" required /><flux:error name="password" /></flux:field>
                <flux:field><flux:label>Confirm password</flux:label><flux:input wire:model="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required /></flux:field>
            </div>
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-950">Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.</div>
            <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs leading-5 text-slate-500">No roles or permission levels are required. Every active account has unified administrator access.</p>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" class="min-h-11 w-full justify-center sm:w-auto" icon="shield-check">Create Administrator</flux:button>
            </div>
        </form>
    </div>
</x-layouts.installer>
