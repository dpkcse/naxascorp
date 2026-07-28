<?php

use App\Models\User;
use Livewire\Volt\Volt as LivewireVolt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = LivewireVolt::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    expect($user->refresh()->last_login_at)->not->toBeNull();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    expect($user->refresh()->last_login_at)->toBeNull();
});

test('inactive users cannot authenticate', function () {
    $user = User::factory()->create(['is_active' => false]);

    LivewireVolt::test('auth.login')->set('email', $user->email)->set('password', 'password')->call('login')->assertHasErrors('email');

    $this->assertGuest();
    expect($user->refresh()->last_login_at)->toBeNull();
});

test('an authenticated inactive user is logged out safely', function () {
    $user = User::factory()->create(['is_active' => false]);

    $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
