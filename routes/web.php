<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\PublicChromeController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', PublicSiteController::class)->name('home');

Route::middleware(['auth', 'administrator.active', 'installed'])->group(function () {
    Route::get('dashboard', DashboardController::class)->middleware('verified')->name('dashboard');
    Route::get('system/license', [LicenseController::class, 'status'])->name('license.status');
    Route::get('system/license/diagnostics', [LicenseController::class, 'diagnostics'])->name('license.diagnostics');

    Route::prefix('website')->name('admin.')->middleware('throttle:60,1')->controller(PublicChromeController::class)->group(function () {
        Route::get('branding', 'branding')->name('branding.edit'); Route::put('branding', 'saveBranding')->name('branding.update');
        Route::get('header', 'header')->name('header.edit'); Route::put('header', 'saveHeader')->name('header.update');
        Route::get('navigation', 'navigation')->name('navigation.index'); Route::post('navigation', 'storeMenu')->name('navigation.store');
        Route::get('navigation/{menu}', 'editNavigation')->name('navigation.edit'); Route::put('navigation/{menu}', 'updateMenu')->name('navigation.update'); Route::delete('navigation/{menu}', 'deleteMenu')->name('navigation.destroy');
        Route::post('navigation/{menu}/items', 'saveItem')->name('navigation.items.store'); Route::put('navigation/{menu}/items/{item}', 'saveItem')->name('navigation.items.update'); Route::delete('navigation/{menu}/items/{item}', 'deleteItem')->name('navigation.items.destroy'); Route::post('navigation/{menu}/items/{item}/move', 'moveItem')->name('navigation.items.move');
        Route::get('footer', 'footer')->name('footer.edit'); Route::put('footer', 'saveFooter')->name('footer.update'); Route::post('footer/columns', 'storeColumn')->name('footer.columns.store'); Route::delete('footer/columns/{column}', 'deleteColumn')->name('footer.columns.destroy'); Route::post('footer/columns/{column}/links', 'storeFooterLink')->name('footer.links.store'); Route::delete('footer/links/{link}', 'deleteFooterLink')->name('footer.links.destroy'); Route::post('footer/social', 'storeSocial')->name('footer.social.store'); Route::delete('footer/social/{social}', 'deleteSocial')->name('footer.social.destroy');
    });
});

Route::middleware(['auth', 'administrator.active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
