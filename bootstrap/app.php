<?php

use App\Http\Middleware\EnsureInstallerAccessible;
use App\Http\Middleware\EnsurePreviousInstallerStep;
use App\Http\Middleware\ProtectInstallerResponse;
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
        $middleware->alias([
            'installer.accessible' => EnsureInstallerAccessible::class,
            'installer.previous' => EnsurePreviousInstallerStep::class,
            'installer.protected' => ProtectInstallerResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
