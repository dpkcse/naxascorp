<?php

namespace App\Http\Middleware;

use App\Domain\Installation\InstallationManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePreviousInstallerStep
{
    public function __construct(private readonly InstallationManager $manager) {}

    public function handle(Request $request, Closure $next, string $step): Response
    {
        if (! $this->manager->canAccess($step)) {
            return redirect()->route($this->manager->redirectRouteFor($step));
        }

        return $next($request);
    }
}
