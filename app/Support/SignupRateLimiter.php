<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class SignupRateLimiter
{
    // Password and social registration share the same connection/device budgets.
    public function hit(Request $request): void
    {
        $ip = sha1((string) $request->ip());
        $limits = [
            'auth-signup-ip-m:'.$ip => [(int) config('security.signup_ip_per_minute'), 60],
            'auth-signup-ip-h:'.$ip => [(int) config('security.signup_ip_per_hour'), 3600],
            'auth-signup-ip-d:'.$ip => [(int) config('security.signup_ip_per_day'), 86400],
        ];
        $device = DeviceIdentity::id($request);
        if ($device !== null) {
            $limits['auth-signup-dev-d:'.sha1($device)] = [(int) config('security.signup_device_per_day'), 86400];
        }
        foreach ($limits as $key => [$max, $seconds]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw ValidationException::withMessages(['phone' => ['Too many accounts have been created from this connection. Please try again later.']]);
            }
        }
        foreach ($limits as $key => [$max, $seconds]) {
            RateLimiter::hit($key, $seconds);
        }
    }
}
