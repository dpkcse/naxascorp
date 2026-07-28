<?php

use App\Http\Middleware\EnsureInstallerAccessible;
use App\Http\Middleware\EnsurePreviousInstallerStep;
use App\Http\Middleware\ProtectInstallerResponse;
use App\Http\Middleware\ActivateConfiguredDatabase;
use App\Http\Middleware\EnsureAdministratorIsActive;
use App\Http\Middleware\EnsureAdministratorCreated;
use App\Http\Middleware\PreventPublicRegistration;
use App\Http\Middleware\EnsureApplicationIsInstalled;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: function (): void {
            require base_path('routes/installer.php');
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [ActivateConfiguredDatabase::class]);
        $middleware->alias([
            'installer.accessible' => EnsureInstallerAccessible::class,
            'installer.previous' => EnsurePreviousInstallerStep::class,
            'installer.protected' => ProtectInstallerResponse::class,
            'administrator.active' => EnsureAdministratorIsActive::class,
            'administrator.created' => EnsureAdministratorCreated::class,
            'registration.closed' => PreventPublicRegistration::class,
            'installed' => EnsureApplicationIsInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
