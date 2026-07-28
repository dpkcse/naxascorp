<?php

use App\Domain\Installation\InstallationState;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

function installerState(): InstallationState
{
    return new InstallationState(new Store('installer-test', new ArraySessionHandler(120)));
}

test('installation progress is ordered and can be invalidated', function () {
    $state = installerState();
    $state->markCompleted('welcome_completed');
    $state->markCompleted('requirements_passed');
    $state->markCompleted('permissions_passed');
    $state->markCompleted('database_connection_verified');
    $state->markCompleted('administrator_created');

    expect($state->previousStepIsComplete('requirements_passed'))->toBeTrue()
        ->and($state->previousStepIsComplete('database_connection_verified'))->toBeTrue();

    $state->forgetFrom('requirements_passed');

    expect($state->hasCompleted('welcome_completed'))->toBeTrue()
        ->and($state->hasCompleted('requirements_passed'))->toBeFalse()
        ->and($state->hasCompleted('permissions_passed'))->toBeFalse()
        ->and($state->hasCompleted('administrator_created'))->toBeFalse();
});

test('installation progress can be reset without application data', function () {
    $state = installerState();
    $state->markCompleted('welcome_completed');
    $state->reset();

    expect($state->hasCompleted('welcome_completed'))->toBeFalse();
});
