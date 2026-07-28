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
        Volt::route('/ready', 'installer.ready')
            ->middleware(['administrator.created', 'auth', 'administrator.active'])
            ->name('ready');
        Volt::route('/website', 'installer.website')
            ->middleware(['installer.previous:website_settings_saved', 'administrator.created', 'auth', 'administrator.active'])
            ->name('website');
        Volt::route('/demo-content', 'installer.demo-content')
            ->middleware(['installer.previous:demo_content_selected', 'administrator.created', 'auth', 'administrator.active'])
            ->name('demo-content');
    });

Route::middleware(['web', 'installer.protected', 'auth', 'administrator.active'])
    ->prefix('install')->name('installer.')
    ->group(function () {
        Volt::route('/complete', 'installer.complete')->name('complete');
        Volt::route('/license/diagnostics', 'installer.license-diagnostics')->name('license.diagnostics');
    });
