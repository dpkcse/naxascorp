<?php

namespace App\Http\Middleware;

use App\Domain\Installation\InstalledState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationIsInstalled
{
    public function __construct(private readonly InstalledState $installedState) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->installedState->isInstalled()) {
            return redirect()->route('installer.welcome');
        }

        return $next($request);
    }
}
