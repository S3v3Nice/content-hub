<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RequireAdmin
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (!Auth::user()->is_admin) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
