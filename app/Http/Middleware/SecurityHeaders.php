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
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        /*
         * Production remains strict:
         * - inline scripts/styles must carry the per-request nonce
         * - no arbitrary Vite dev origins are allowed
         *
         * Local Vite development is different: Vite's HMR client injects
         * stylesheet <style> tags and opens a WebSocket on port 5173.
         * Those tags cannot receive Laravel's per-response nonce, so allowing
         * the dev HMR channel is necessary while APP_ENV=local.
         */
        $isLocal = app()->environment('local');

        // Google Maps (checkout's delivery-location picker) injects scripts and
        // styles from maps.googleapis.com/maps.gstatic.com, geocodes over fetch,
        // and paints tiles from maps.gstatic.com — each origin is added narrowly
        // below rather than relaxing these directives generally.
        $scriptSrc = [
            "'self'",
            "'nonce-{$nonce}'",
            'https://api.mapbox.com',
            'https://maps.googleapis.com',
            'https://maps.gstatic.com',
        ];

        $styleSrc = [
            "'self'",
            'https://fonts.googleapis.com',
            'https://api.mapbox.com',
            'https://maps.gstatic.com',
        ];

        $styleSrcElem = [
            "'self'",
            "'nonce-{$nonce}'",
            'https://fonts.googleapis.com',
            'https://api.mapbox.com',
            'https://maps.gstatic.com',
        ];

        $connectSrc = [
            "'self'",
            'https://api.mapbox.com',
            'https://events.mapbox.com',
            'https://maps.googleapis.com',
            'https://maps.gstatic.com',
        ];

        if ($isLocal) {
            // Vite is pinned to IPv4 loopback in vite.config.js so the
            // browser sees a CSP source that matches the actual dev origin.
            $scriptSrc[] = 'http://127.0.0.1:5173';
            $scriptSrc[] = 'http://localhost:5173';

            // Vite HMR creates <style> elements at runtime. CSP nonce sources
            // suppress 'unsafe-inline' when they are present in the same directive,
            // so local development must use a separate style-src-elem policy
            // without the nonce. This is intentionally local-only.
            $styleSrc[] = "'unsafe-inline'";
            $styleSrc[] = 'http://127.0.0.1:5173';
            $styleSrc[] = 'http://localhost:5173';

            $styleSrcElem = [
                "'self'",
                "'unsafe-inline'",
                'http://127.0.0.1:5173',
                'http://localhost:5173',
                'https://fonts.googleapis.com',
                'https://api.mapbox.com',
                'https://maps.gstatic.com',
            ];

            $connectSrc[] = 'http://127.0.0.1:5173';
            $connectSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'ws://127.0.0.1:5173';
            $connectSrc[] = 'ws://localhost:5173';
        }

        $policy = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "frame-src 'none'",
            "form-action 'self'",
            'script-src '.implode(' ', $scriptSrc),
            'style-src '.implode(' ', $styleSrc),
            'style-src-elem '.implode(' ', $styleSrcElem),
            "style-src-attr 'unsafe-inline'",
            "font-src 'self' data: https://fonts.gstatic.com",
            'img-src '.config('security.csp_img_src', "'self' data: blob: https:"),
            'connect-src '.implode(' ', $connectSrc),
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
