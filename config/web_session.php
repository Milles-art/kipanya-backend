<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Session Cookie
    |--------------------------------------------------------------------------
    |
    | The browser client authenticates against the API using a Sanctum token
    | that is delivered in an HttpOnly cookie rather than exposed to
    | JavaScript. The cookie value mirrors the bearer token, so the API and all
    | existing authorization logic remain unchanged for other clients.
    |
    */

    'cookie' => env('KP_WEB_SESSION_COOKIE', 'kp_web_session'),

    'path' => '/',
    'domain' => env('KP_WEB_SESSION_DOMAIN', null),
    'secure' => (bool) env('SESSION_SECURE_COOKIE', env('APP_FORCE_HTTPS', false)),
    'http_only' => true,
    'same_site' => 'lax',

];
