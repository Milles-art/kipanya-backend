<?php

/*
| Origins are sanitized here rather than trusted verbatim, so a stray
| wildcard or empty entry in CORS_ALLOWED_ORIGINS can never reach the CORS
| layer even in an environment where ProductionSecurityGuard does not run.
| The localhost fallback applies to non-production only; production must
| name its real frontend host explicitly or no cross-origin client is allowed.
*/

$corsIsLocal = env('APP_ENV', 'production') !== 'production';

$corsOrigins = array_values(array_filter(
    array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))),
    static function (string $origin) use ($corsIsLocal): bool {
        if ($origin === '' || $origin === '*') {
            return false;
        }

        if ($corsIsLocal) {
            return true;
        }

        // Production: only explicit https origins, never loopback or wildcards.
        if (! str_starts_with(strtolower($origin), 'https://')) {
            return false;
        }

        return ! str_contains(strtolower($origin), 'localhost')
            && ! str_contains(strtolower($origin), '127.0.0.1');
    },
));

if ($corsOrigins === [] && ! $corsIsLocal) {
    $corsOrigins = [];
} elseif ($corsOrigins === []) {
    $corsOrigins = ['http://localhost:3000'];
}

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => $corsOrigins,
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Accept', 'Authorization', 'Content-Type', 'Origin', 'X-Requested-With',
        'X-Guest-Cart-Token', 'Idempotency-Key', 'X-Request-ID',
    ],
    'exposed_headers' => ['X-Request-ID'],
    'max_age' => 86400,
    'supports_credentials' => false,
];
