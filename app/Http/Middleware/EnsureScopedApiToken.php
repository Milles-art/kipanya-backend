<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class EnsureScopedApiToken
{
    /**
     * Enforce token ability scopes for token-authenticated requests while
     * staying permissive for web session and `actingAs` requests that carry no
     * access token (their scope is already bounded by their own guards).
     * Tokens issued with a wildcard ability are always rejected with 403 and
     * logged as a security signal, since the production issuer never creates
     * them.
     */
    public function handle(Request $request, Closure $next, string ...$abilities)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'currentAccessToken')) {
            return $next($request);
        }

        $token = $user->currentAccessToken();

        if (! $token) {
            return $next($request);
        }

        if (in_array('*', (array) $token->abilities, true)) {
            Log::warning('Wildcard API token rejected on a scoped route', [
                'user_id' => $user->getKey(),
                'token_id' => $token->id,
                'request_id' => $request->attributes->get('request_id') ?? null,
            ]);

            abort(403, 'This token is not permitted for scoped API access.');
        }

        $granted = (array) $token->abilities;

        foreach ($abilities as $ability) {
            if (! in_array($ability, $granted, true)) {
                abort(403, 'Your token does not have permission to perform this action.');
            }
        }

        return $next($request);
    }
}
