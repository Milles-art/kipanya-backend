<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Administrator two-factor enforcement
    |--------------------------------------------------------------------------
    | When true, every admin must enrol TOTP before reaching any admin page.
    | Admin sign-in is otherwise SMS-only, which is exposed to SIM-swap.
    */
    'admin_require_2fa' => (bool) env('ADMIN_REQUIRE_2FA', true),

    /*
    |--------------------------------------------------------------------------
    | OTP request limits (SMS cost / pumping protection)
    |--------------------------------------------------------------------------
    | Per-IP and global ceilings on requests that send an SMS. Per-phone limits
    | live in OtpService.
    */
    'otp_ip_per_minute' => (int) env('OTP_IP_PER_MINUTE', 10),
    'otp_ip_per_hour' => (int) env('OTP_IP_PER_HOUR', 20),
    'otp_global_per_day' => (int) env('OTP_GLOBAL_PER_DAY', 3000),

    /*
    |--------------------------------------------------------------------------
    | Content-Security-Policy img-src
    |--------------------------------------------------------------------------
    | Defaults to any HTTPS host because product images may be third-party.
    | Tighten (e.g. "'self' data: blob: https://cdn.example.com") once pinned.
    */
    'csp_img_src' => env('CSP_IMG_SRC', "'self' data: blob: https:"),

    /*
    |--------------------------------------------------------------------------
    | Initial administrator (read by AdminUserSeeder)
    |--------------------------------------------------------------------------
    | Seeders must not call env() directly: with `config:cache` it returns null.
    */
    'seed_admin' => [
        'phone' => env('ADMIN_PHONE', ''),
        'email' => env('ADMIN_EMAIL', ''),
        'name' => env('ADMIN_NAME', 'KP Wear Administrator'),
    ],

];
