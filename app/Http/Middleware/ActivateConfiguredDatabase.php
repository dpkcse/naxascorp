<?php

namespace App\Http\Middleware;

use App\Domain\Installation\DatabaseConfigurationActivator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ActivateConfiguredDatabase
{
    public function __construct(private readonly DatabaseConfigurationActivator $activator) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->activator->activate();
        } catch (Throwable) {
            // The installer renders safe recovery guidance when its encrypted handoff is unavailable.
        }

        return $next($request);
    }
}
