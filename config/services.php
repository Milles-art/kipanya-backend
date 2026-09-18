<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notify Africa
    |--------------------------------------------------------------------------
    |
    | Server-side SMS delivery for OTP codes. An empty API key is allowed in
    | local development so the app keeps using LogSmsGateway; production
    | refuses to boot without a real key (see ProductionSecurityGuard).
    |
    */

    'notify_africa' => [
        'base_url' => env('NOTIFY_AFRICA_BASE_URL', 'https://api.notify.africa'),
        'api_key' => env('NOTIFY_AFRICA_API_KEY'),
        'sender_id' => env('NOTIFY_AFRICA_SENDER_ID'),
        'timeout' => (int) env('NOTIFY_AFRICA_TIMEOUT', 10),
    ],

];
