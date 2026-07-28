<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventPublicRegistration
{
    public function handle(Request $request, Closure $next): Response
    {
        return redirect()->route('login')->with('status', 'Public registration is unavailable. Sign in with the administrator account created during installation.');
    }
}
