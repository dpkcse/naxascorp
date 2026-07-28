<?php

use App\Domain\Installation\DatabaseConfigurationActivator;
use App\Domain\Installation\InstallationState;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

use function Pest\Laravel\mock;

beforeEach(function () {
    session()->put('installer.progress', [
        'welcome_completed' => true,
        'requirements_passed' => true,
        'permissions_passed' => true,
        'database_connection_verified' => true,
    ]);

    mock(DatabaseConfigurationActivator::class)->shouldReceive('activate')->zeroOrMoreTimes()->andReturnTrue();
});

test('administrator page displays the protected premium form', function () {
    $this->get('/install/administrator')
        ->assertSuccessful()
        ->assertSeeTextInOrder(['Administrator Account', 'Full name', 'Email address', 'Password', 'Confirm password', 'Create Administrator', 'Naxas Innovations Limited'])
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Cache-Control', 'no-store, private, max-age=0');
});

test('administrator input is validated and passwords are cleared', function () {
    Volt::test('installer.administrator')
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->set('password', 'weak')
        ->set('password_confirmation', 'different')
        ->call('createAdministrator')
        ->assertHasErrors(['name', 'email', 'password'])
        ->assertSet('password', '')
        ->assertSet('password_confirmation', '');

    expect(app(InstallationState::class)->hasCompleted('administrator_created'))->toBeFalse();
});

test('initial administrator is normalized hashed active and created once', function () {
    $component = Volt::test('installer.administrator')
        ->set('name', '  Alex   Morgan  ')
        ->set('email', 'ADMIN@EXAMPLE.COM')
        ->set('password', 'Secure!Password2')
        ->set('password_confirmation', 'Secure!Password2')
        ->call('createAdministrator')
        ->assertHasNoErrors()
        ->assertSet('password', '')
        ->assertSet('password_confirmation', '')
        ->assertRedirect(route('installer.license'));

    $administrator = User::query()->sole();
    expect($administrator->name)->toBe('Alex Morgan')
        ->and($administrator->email)->toBe('admin@example.com')
        ->and($administrator->is_active)->toBeTrue()
        ->and(Hash::check('Secure!Password2', $administrator->password))->toBeTrue()
        ->and(app(InstallationState::class)->hasCompleted('administrator_created'))->toBeTrue();

    $component->call('createAdministrator');
    expect(User::query()->count())->toBe(1);
});

test('existing user conflict is safe and does not mark administrator complete', function () {
    User::factory()->create();

    Volt::test('installer.administrator')
        ->set('name', 'Alex Morgan')->set('email', 'alex@example.com')
        ->set('password', 'Secure!Password2')->set('password_confirmation', 'Secure!Password2')
        ->call('createAdministrator')->assertHasErrors('administrator');

    expect(User::query()->count())->toBe(1)
        ->and(app(InstallationState::class)->hasCompleted('administrator_created'))->toBeFalse();
});

test('direct administrator action cannot skip database verification', function () {
    session()->forget('installer.progress.database_connection_verified');

    Volt::test('installer.administrator')->call('createAdministrator')->assertRedirect(route('installer.database'));

    expect(User::query()->count())->toBe(0);
});
