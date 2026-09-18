<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject bearers belonging to deactivated accounts. Sanctum validates the
 * token itself but does not inspect the account status, so an administrator
 * deactivating a user must also be able to invalidate in-flight sessions.
 */
final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isActive()) {
            abort(403, 'This account is not active.');
        }

        return $next($request);
    }
}
