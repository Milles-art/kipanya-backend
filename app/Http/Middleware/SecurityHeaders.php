<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Str::random(40);
        Vite::useCspNonce($nonce);

        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        // Isolate this site's browsing context group from cross-origin windows (Spectre-class
        // and window.opener attacks). The payment redirect is a top-level navigation, not a popup.
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        // Inline <script>/<style> blocks carry a per-request nonce and inline
        // event-handler attributes were removed, so `script-src` no longer
        // needs 'unsafe-inline'. Inline style attributes (style="...") cannot
        // be nonced, so they remain allowed through the scoped `style-src-attr`.
        // `img-src ... https:` stays broad on purpose: product and campaign
        // imagery is currently served from arbitrary third-party HTTPS hosts;
        // replace the scheme with an explicit host allowlist once image
        // origins are pinned. `worker-src`/`manifest-src`/`frame-src` are
        // locked down because the storefront uses none of them.
        // Mapbox GL JS (checkout's delivery-location picker) loads its script/CSS
        // from api.mapbox.com, calls the Mapbox geocoding + tile APIs over fetch,
        // sends anonymous usage pings to events.mapbox.com, and parses vector
        // tiles in a blob: web worker — each is added narrowly below rather than
        // relaxing these directives generally.
        $policy = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "frame-src 'none'",
            "form-action 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://api.mapbox.com",
            "style-src 'self' https://fonts.googleapis.com https://api.mapbox.com",
            "style-src-elem 'self' 'nonce-{$nonce}' https://fonts.googleapis.com https://api.mapbox.com",
            "style-src-attr 'unsafe-inline'",
            "font-src 'self' data: https://fonts.gstatic.com",
            'img-src '.config('security.csp_img_src', "'self' data: blob: https:"),
            "connect-src 'self' https://api.mapbox.com https://events.mapbox.com",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $policy);

        if ($request->isSecure() || (bool) config('app.force_https', false)) {
            $response->headers->set('Content-Security-Policy', $policy.'; upgrade-insecure-requests');
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
