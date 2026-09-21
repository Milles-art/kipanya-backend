<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminWebAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        abort_unless($user->isActive() && $user->isAdmin(), 403);

        return $next($request);
    }
}
