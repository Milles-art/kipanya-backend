<?php

namespace App\Support;

use App\Services\Auth\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Fresh second-factor confirmation for high-impact admin actions (money). A stolen or
 * hijacked admin session cannot perform them without the admin's authenticator app.
 */
final class AdminStepUp
{
    private const MAX_FAILURES = 5;

    public function __construct(private readonly TotpService $totp) {}

    public static function enabled(): bool
    {
        return (bool) config('security.money_actions_require_totp', true);
    }

    public function assert(Request $request): void
    {
        if (! self::enabled()) {
            return;
        }

        $user = $request->user();

        if (! $user || ! $user->twoFactorEnabled()) {
            throw ValidationException::withMessages([
                'totp_code' => 'Enable two-factor authentication on your account to perform this action.',
            ]);
        }

        $data = $request->validate(['totp_code' => ['required', 'digits:6']]);

        $key = 'admin-stepup:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_FAILURES)) {
            throw ValidationException::withMessages(['totp_code' => 'Too many attempts. Please try again later.']);
        }

        if (! $this->totp->verifyAndConsume((string) $user->two_factor_secret, $data['totp_code'])) {
            RateLimiter::hit($key, 600);

            throw ValidationException::withMessages(['totp_code' => 'The authenticator code is incorrect.']);
        }

        RateLimiter::clear($key);
    }
}
