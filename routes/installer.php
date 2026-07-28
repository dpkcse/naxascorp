<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['web', 'installer.accessible', 'installer.protected'])
    ->prefix('install')
    ->name('installer.')
    ->group(function () {
        Volt::route('/', 'installer.welcome')->name('welcome');
        Volt::route('/requirements', 'installer.requirements')
            ->middleware('installer.previous:requirements_passed')
            ->name('requirements');
        Volt::route('/permissions', 'installer.permissions')
            ->middleware('installer.previous:permissions_passed')
            ->name('permissions');
        Volt::route('/database', 'installer.database')
            ->middleware('installer.previous:database_connection_verified')
            ->name('database');
        Volt::route('/administrator', 'installer.administrator')
            ->middleware('installer.previous:administrator_created')
            ->name('administrator');
        Volt::route('/handoff', 'installer.handoff')
            ->middleware('administrator.created')
            ->name('handoff');
        Volt::route('/license', 'installer.license')
            ->middleware(['administrator.created', 'auth', 'administrator.active'])
            ->name('license');
        Volt::route('/license/diagnostics', 'installer.license-diagnostics')
            ->middleware(['administrator.created', 'auth', 'administrator.active'])
            ->name('license.diagnostics');
        Volt::route('/ready', 'installer.ready')
            ->middleware(['administrator.created', 'auth', 'administrator.active'])
            ->name('ready');
    });
