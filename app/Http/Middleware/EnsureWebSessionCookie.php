<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class EnsureWebSessionCookie
{
    /**
     * Advertise a valid browser session as a bearer token for requests that do
     * not already carry an Authorization header. The cookie is HttpOnly, so
     * JavaScript never sees the credential, while the rest of the API stack
     * (Sanctum guard, token abilities, logout revocation) keeps working.
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            ! $request->headers->has('Authorization')
            && $request->cookies->has(config('web_session.cookie'))
        ) {
            $request->headers->set(
                'Authorization',
                'Bearer '.$request->cookies->get(config('web_session.cookie')),
            );
        }

        return $next($request);
    }
}
