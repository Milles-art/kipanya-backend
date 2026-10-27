<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Give every visitor a stable first-party device identifier.
 *
 * The cookie carries a random opaque id (HttpOnly, so page JavaScript never
 * sees it). It exists purely as an anti-farm signal: at account creation the
 * server stores an HMAC of this value on the user row, which lets the admin
 * panel flag several accounts born on one browser. It is deliberately NOT a
 * browser fingerprint — clearing cookies resets it, so it raises the cost of
 * bulk sign-ups without tracking people across sites.
 */
final class EnsureDeviceCookie
{
    public const COOKIE = 'kp_device';

    /** One year, in minutes. */
    public const LIFETIME_MINUTES = 525600;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->cookies->has(self::COOKIE)) {
            return $response;
        }

        // Transport settings mirror the web-session cookie so both travel the
        // same way; the value itself is not a secret, so it is excluded from
        // cookie encryption alongside kp_web_session (see ProxyTrust).
        return $response->withCookie(
            Cookie::make(
                self::COOKIE,
                bin2hex(random_bytes(16)),
                self::LIFETIME_MINUTES,
                (string) config('web_session.path', '/'),
                config('web_session.domain'),
                (bool) config('web_session.secure'),
                true,
                false,
                (string) config('web_session.same_site', 'lax'),
            ),
        );
    }
}
