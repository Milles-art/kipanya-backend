<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'))))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Accept', 'Authorization', 'Content-Type', 'Origin', 'X-Requested-With',
        'X-Guest-Cart-Token', 'Idempotency-Key', 'X-Request-ID',
    ],
    'exposed_headers' => ['X-Request-ID'],
    'max_age' => 86400,
    'supports_credentials' => false,
];
