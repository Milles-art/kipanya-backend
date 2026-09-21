<?php

namespace App\Support;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Http\Middleware\TrustProxies;

/**
 * Applies deployment-dependent request settings from CONFIG, not from env().
 *
 * env() must only be read inside config files: once `php artisan config:cache`
 * runs (the normal production deploy) env() returns null everywhere else, which
 * silently disabled proxy trust while the production guard - which reads config -
 * still passed. Reading config keeps the guard and the behaviour in agreement.
 */
final class ProxyTrust
{
    public static function apply(): void
    {
        $proxies = config('app.trusted_proxies');

        if (is_string($proxies) && trim($proxies) !== '') {
            $proxies = trim($proxies);

            // '*' trusts every hop and lets any client spoof X-Forwarded-For (and so
            // bypass IP rate limits) if the origin is reachable directly. Prefer the
            // proxy's real addresses and firewall the origin.
            TrustProxies::at($proxies === '*' ? '*' : array_values(array_filter(array_map('trim', explode(',', $proxies)))));
        }

        // The web-session cookie is an opaque bearer token validated server side, so
        // Laravel's cookie encryption adds nothing for it.
        EncryptCookies::except([(string) config('web_session.cookie', 'kp_web_session')]);
    }
}
