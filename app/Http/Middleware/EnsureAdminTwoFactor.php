<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin sign-in is an SMS one-time code. SMS alone is exposed to SIM-swap, so
 * every administrator must enrol TOTP two-factor authentication before they can
 * use any admin page other than the enrolment screen and logout.
 */
final class EnsureAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && config('security.admin_require_2fa', true)
            && ! $user->twoFactorEnabled()
            && ! $request->routeIs('admin.security.two-factor.*', 'admin.logout')
        ) {
            return redirect()
                ->route('admin.security.two-factor.index')
                ->with('info', 'Two-factor authentication is required for administrators. Please enable it to continue.');
        }

        return $next($request);
    }
}
