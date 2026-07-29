<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\PublicChromeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', PublicSiteController::class)->name('home');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

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

    Route::prefix('website/homepage')->name('admin.homepage.')->middleware('throttle:60,1')->controller(HomepageController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'saveSettings')->name('update');
        Route::post('publish', 'publish')->name('publish');
        Route::post('unpublish', 'unpublish')->name('unpublish');
        Route::get('preview', 'preview')->name('preview');
        Route::get('sections', 'edit')->name('sections');
        Route::get('{section}', 'section')->where('section', '[a-z_]+')->name('section');
        Route::put('sections/{section}', 'saveSection')->name('sections.update');
        Route::post('sections/{section}/move', 'move')->name('sections.move');
        Route::post('sections/{section}/items', 'storeItem')->name('items.store');
        Route::delete('sections/{section}/items/{item}', 'destroyItem')->name('items.destroy');
    });

    Route::prefix('website/pages')->name('admin.pages.')->middleware('throttle:60,1')->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index'); Route::get('create', [PageController::class, 'create'])->name('create'); Route::post('/', [PageController::class, 'store'])->name('store');
        Route::get('{page}/edit', [PageController::class, 'edit'])->name('edit'); Route::match(['put', 'patch'], '{page}', [PageController::class, 'update'])->name('update');
        Route::post('{page}/publish', [PageController::class, 'publish'])->name('publish'); Route::post('{page}/schedule', [PageController::class, 'schedule'])->name('schedule'); Route::post('{page}/unpublish', [PageController::class, 'unpublish'])->name('unpublish');
        Route::post('{page}/archive', [PageController::class, 'archive'])->name('archive'); Route::post('{page}/restore', [PageController::class, 'restore'])->name('restore'); Route::post('{page}/duplicate', [PageController::class, 'duplicate'])->name('duplicate');
        Route::post('{page}/move-up', [PageController::class, 'move'])->defaults('direction', 'up')->name('move-up'); Route::post('{page}/move-down', [PageController::class, 'move'])->defaults('direction', 'down')->name('move-down'); Route::get('{page}/preview', [PageController::class, 'preview'])->name('preview');
        Route::post('{page}/sections', [PageSectionController::class, 'store'])->name('sections.store'); Route::match(['put', 'patch'], '{page}/sections/{section}', [PageSectionController::class, 'update'])->name('sections.update'); Route::delete('{page}/sections/{section}', [PageSectionController::class, 'destroy'])->name('sections.destroy'); Route::post('{page}/sections/{section}/move', [PageSectionController::class, 'move'])->name('sections.move');
    });
});

Route::middleware(['auth', 'administrator.active'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

Route::get('{slug}', PublicPageController::class)->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('pages.show');
