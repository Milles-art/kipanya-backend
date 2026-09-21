<?php

namespace App\Support;

use RuntimeException;

/**
 * Fail-fast guard for insecure production configuration.
 *
 * The deployment posture for HTTPS cookies, encryption at rest, debug output,
 * CORS and OTP debugging is currently driven by environment variables. A
 * misconfigured deployment (or a cached config baked from a local machine)
 * would otherwise silently serve the app in an unsafe state, so we refuse to
 * boot in production until the secure defaults are confirmed.
 */
final class ProductionSecurityGuard
{
    public static function assert(): void
    {
        if (config('app.env', app()->environment()) !== 'production') {
            return;
        }

        $issues = [];

        if (empty(config('app.key'))) {
            $issues[] = 'APP_KEY is not set.';
        }

        if (config('app.debug')) {
            $issues[] = 'APP_DEBUG must be false in production.';
        }

        $hasForcedHttps = (bool) config('app.force_https');
        $appUrl = (string) config('app.url');

        if (! $hasForcedHttps && ! str_starts_with(strtolower($appUrl), 'https://')) {
            $issues[] = 'APP_FORCE_HTTPS must be true (or APP_URL must use https) so cookies and redirects stay on TLS.';
        }

        if (! config('session.secure')) {
            $issues[] = 'SESSION_SECURE_COOKIE must be true in production.';
        }

        if (! config('session.encrypt')) {
            $issues[] = 'SESSION_ENCRYPT must be true in production.';
        }

        $origins = (array) config('cors.allowed_origins', []);

        if (in_array('*', $origins, true)) {
            $issues[] = 'CORS_ALLOWED_ORIGINS must not be a wildcard in production.';
        }

        foreach ($origins as $origin) {
            $origin = strtolower((string) $origin);

            if ($origin === '' || str_contains($origin, 'localhost') || str_contains($origin, '127.0.0.1')) {
                $issues[] = 'CORS_ALLOWED_ORIGINS must only list real frontend hosts in production (got "'.$origin.'").';
            }
        }

        if (config('auth.expose_otp_codes') || config('auth.log_otp_codes')) {
            $issues[] = 'OTP codes would be logged/exposed in production; clear config/auth.php debug flags.';
        }

        if (empty(config('app.trusted_proxies'))) {
            $issues[] = 'TRUSTED_PROXIES must list your reverse proxy IPs (or *) in production so client IPs cannot be spoofed through forwarded headers.';
        }

        if (empty(config('services.notify_africa.api_key'))) {
            $issues[] = 'NOTIFY_AFRICA_API_KEY must be set in production so OTP SMS can be delivered instead of the log gateway.';
        }

        if (! config('security.admin_require_2fa', true)) {
            $issues[] = 'ADMIN_REQUIRE_2FA must be true in production: admin sign-in by SMS alone is exposed to SIM-swap.';
        }

        // The payment gateway otherwise fails only when the first customer tries to pay.
        foreach ([
            'base_url' => 'SELCOM_BASE_URL',
            'api_key' => 'SELCOM_API_KEY',
            'api_secret' => 'SELCOM_API_SECRET',
            'vendor_id' => 'SELCOM_VENDOR_ID',
            'webhook_url' => 'SELCOM_WEBHOOK_URL',
        ] as $key => $envName) {
            if (! is_string(config("services.selcom.$key")) || trim((string) config("services.selcom.$key")) === '') {
                $issues[] = $envName.' must be set in production so customers can pay and payments can be confirmed.';
            }
        }

        $selcomBase = (string) config('services.selcom.base_url');
        if ($selcomBase !== '' && ! str_starts_with(strtolower($selcomBase), 'https://')) {
            $issues[] = 'SELCOM_BASE_URL must use https:// in production.';
        }

        if ($issues !== []) {
            throw new RuntimeException("Insecure production configuration detected:\n - ".implode("\n - ", $issues));
        }
    }
}
