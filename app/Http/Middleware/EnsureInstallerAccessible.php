<?php

namespace App\Http\Middleware;

use App\Domain\Installation\InstallationManager;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallerAccessible
{
    public function __construct(private readonly InstallationManager $manager) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if ($this->manager->isInstalled()) {
            return redirect()->route($request->user()?->is_active ? 'dashboard' : 'login');
        }

        return $next($request);
    }
}
