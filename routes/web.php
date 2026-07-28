<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', PublicSiteController::class)->name('home');

Route::middleware(['auth', 'administrator.active', 'installed'])->group(function () {
    Route::get('dashboard', DashboardController::class)->middleware('verified')->name('dashboard');
    Route::get('system/license', [LicenseController::class, 'status'])->name('license.status');
    Route::get('system/license/diagnostics', [LicenseController::class, 'diagnostics'])->name('license.diagnostics');
});

Route::middleware(['auth', 'administrator.active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
