<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotAdmin
{
    /**
     * Handle an incoming request.
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (!Auth::user()->is_admin){
            throw new AuthenticationException(
                'Unauthenticated.', $guards, $request->expectsJson() ? null : '/'
            );
        }

        return $next($request);
    }
}
