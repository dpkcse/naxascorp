<?php

test('public registration redirects to login', function () {
    $this->get('/register')->assertRedirect(route('login'));
});

test('registration cannot create an account before administrator setup', function () {
    Livewire\Volt\Volt::test('auth.register')->call('register')->assertRedirect(route('installer.welcome'));

    expect(App\Models\User::query()->count())->toBe(0);
});
