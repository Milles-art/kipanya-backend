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
     *
     * SECURITY (CSRF): browsers attach the cookie automatically, so a
     * cookie-authenticated request that changes state must prove it comes from
     * this site. SameSite=Lax is only one layer (it does not stop same-site
     * attackers, e.g. a hostile sibling subdomain), so unsafe methods that rely on
     * the cookie must additionally be same-origin. Requests that carry their own
     * Authorization header (mobile apps, scripts) are not cookie-authenticated and
     * are unaffected.
     */
    public function handle(Request $request, Closure $next)
    {
        $usedCookie = false;

        if (
            ! $request->headers->has('Authorization')
            && $request->cookies->has(config('web_session.cookie'))
        ) {
            $request->headers->set(
                'Authorization',
                'Bearer '.$request->cookies->get(config('web_session.cookie')),
            );
            $usedCookie = true;
        }

        if ($usedCookie && ! $request->isMethodSafe() && ! $this->isSameOrigin($request)) {
            return response()->json(['message' => 'Cross-site request blocked.'], 403);
        }

        return $next($request);
    }

    private function isSameOrigin(Request $request): bool
    {
        // Sent by every modern browser and not forgeable from a web page.
        $fetchSite = $request->headers->get('Sec-Fetch-Site');

        if ($fetchSite !== null) {
            return $fetchSite === 'same-origin';
        }

        $self = $request->getSchemeAndHttpHost();

        $origin = $request->headers->get('Origin');

        if ($origin !== null) {
            return $this->sameOrigin($origin, $self);
        }

        $referer = $request->headers->get('Referer');

        // No provenance header at all on a state-changing cookie request: refuse.
        return $referer !== null && $this->sameOrigin($referer, $self);
    }

    private function sameOrigin(string $url, string $self): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        $origin = strtolower($parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : ''));

        return hash_equals(strtolower($self), $origin);
    }
}
