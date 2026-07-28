<?php

namespace App\Http\Middleware;

use App\Domain\Installation\AdministratorLifecycle;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministratorCreated
{
    public function __construct(private readonly AdministratorLifecycle $lifecycle) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->lifecycle->hasAdministrator()) {
            return redirect()->route('installer.administrator');
        }

        return $next($request);
    }
}
