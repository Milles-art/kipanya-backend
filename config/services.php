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

    'notify_africa' => [
        'base_url' => env('NOTIFY_AFRICA_BASE_URL', 'https://api.notify.africa'),
        'api_key' => env('NOTIFY_AFRICA_API_KEY'),
        'sender_id' => env('NOTIFY_AFRICA_SENDER_ID'),
        'timeout' => (int) env('NOTIFY_AFRICA_TIMEOUT', 10),
    ],




    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Selcom Checkout API
    |--------------------------------------------------------------------------
    |
    | Server-side payment gateway for the Checkout API (developers.selcommobile.com).
    | Credentials stay server-side; an empty API key/secret/vendor in local
    | development keeps the FakePaymentGateway active. Production must provide
    | all of SELCOM_BASE_URL, SELCOM_API_KEY, SELCOM_API_SECRET and
    | SELCOM_VENDOR_ID or checkout will refuse to run.
    |
    */

    'selcom' => [
        'base_url' => env('SELCOM_BASE_URL'),
        'api_key' => env('SELCOM_API_KEY'),
        'api_secret' => env('SELCOM_API_SECRET'),
        'vendor_id' => env('SELCOM_VENDOR_ID'),
        'currency' => env('SELCOM_CURRENCY', 'TZS'),
        'redirect_url' => env('SELCOM_REDIRECT_URL'),
        'cancel_url' => env('SELCOM_CANCEL_URL'),
        'webhook_url' => env('SELCOM_WEBHOOK_URL'),
        'timeout' => (int) env('SELCOM_TIMEOUT', 10),
        // Comma-separated hosts the customer may be redirected to for payment
        // (a host also matches its subdomains). Empty = any https host.
        'allowed_redirect_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('SELCOM_ALLOWED_REDIRECT_HOSTS', '')),
        ))),
    ],

];


