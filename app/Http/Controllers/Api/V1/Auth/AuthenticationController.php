<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\IssueSanctumToken;
use App\Actions\Auth\RegisterUser;
use App\DTOs\Auth\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\CheckoutSigninRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Mail\ResetPasswordLink;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\DeviceIdentity;
use App\Support\PhoneNumber;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Password-based storefront authentication.
 *
 * Phone number is the identifier; password grants access; recovery is a free
 * email link (no SMS anywhere). All credential failures share one uniform
 * message so the endpoints are not an account-existence oracle, and every
 * rate-limit write happens OUTSIDE database transactions so a failed attempt
 * can never be rolled back into "no attempt at all".
 */
final class AuthenticationController extends Controller
{
    /** Password-reset link validity window. */
    private const RESET_TOKEN_TTL_MINUTES = 60;

    /** Failed sign-ins allowed per phone+IP before the lockout window. */
    private const LOGIN_MAX_ATTEMPTS = 5;

    /** Lockout window after LOGIN_MAX_ATTEMPTS failed sign-ins (seconds). */
    private const LOGIN_LOCKOUT_SECONDS = 600;

    /** Reset requests allowed per phone+IP per window. */
    private const FORGOT_MAX_ATTEMPTS = 5;

    /** Reset requests allowed per IP per window — blocks rotating phones to
     *  mail-bomb one victim's inbox. */
    private const FORGOT_IP_MAX_ATTEMPTS = 20;

    private const FORGOT_WINDOW_SECONDS = 3600;

    /** Reset-password submissions per IP (brute-force backstop; the token
     *  itself is 32 random bytes, so this only narrows the window). */
    private const RESET_MAX_ATTEMPTS = 10;

    private const RESET_WINDOW_SECONDS = 600;

    public function register(
        RegisterRequest $request,
        RegisterUser $registerUser,
        IssueSanctumToken $tokenIssuer,
        AuditLogger $auditLogger,
    ): JsonResponse {
        $this->guardSignup($request);

        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();
        $email = $request->string('email')->trim()->toString() ?: null;

        // Deliberate exception to uniform responses: sign-up has to say
        // "already registered" so the customer is sent to sign in instead of
        // silently creating an account they can never access again. The
        // per-IP/device caps in guardSignup() bound how fast this can be
        // probed.
        if (User::query()->wherePhone($phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered. Sign in instead.'],
            ]);
        }

        if ($email !== null && User::query()->whereEmail($email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email address is already in use.'],
            ]);
        }

        $user = $this->createAccount($registerUser, new RegisterUserData(
            name: $request->string('name')->trim()->toString(),
            phone: $phone,
            referralCode: $request->string('referral_code')->trim()->toString() ?: null,
            password: $request->string('password')->toString(),
            email: $email,
            deviceHash: DeviceIdentity::hash(DeviceIdentity::id($request)),
            signupIp: $request->ip(),
        ));

        // A brand-new account owns no sessions yet, so no revocation is needed.
        $token = $tokenIssuer->execute($user);

        $auditLogger->log($request, 'auth.registered', $user, ['user_id' => $user->id], $user);

        return $this->withWebSessionCookie(response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
        ], 201), $token);
    }

    public function login(
        LoginRequest $request,
        IssueSanctumToken $tokenIssuer,
        AuditLogger $auditLogger,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();
        $key = $this->loginRateKey($phone, (string) $request->ip());

        if (RateLimiter::tooManyAttempts($key, self::LOGIN_MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'phone' => ['Too many attempts. Please try again in a few minutes.'],
            ]);
        }

        $user = User::query()->wherePhone($phone)->first();

        /*
         * One uniform failure for unknown numbers, wrong passwords, inactive
         * accounts and legacy accounts that have no password yet — this
         * endpoint never confirms that a number has an account.
         */
        if (! $this->canPasswordAuthenticate($user, $request->string('password')->toString())) {
            RateLimiter::hit($key, self::LOGIN_LOCKOUT_SECONDS);

            throw ValidationException::withMessages([
                'phone' => ['The phone number or password you entered is incorrect.'],
            ]);
        }

        RateLimiter::clear($key);

        $token = $this->issueSession($user, $tokenIssuer);

        $auditLogger->log($request, 'auth.login', $user, ['user_id' => $user->id], $user);

        return $this->withWebSessionCookie(response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user),
        ]), $token);
    }

    /**
     * Step 1 of recovery: accept a phone number and, when the account has an
     * email address on file, send a single-use reset link to it.
     *
     * The response is identical whether or not the account exists (or has an
     * email), so the endpoint cannot be used to enumerate customers; the page
     * copy explains both outcomes instead.
     */
    public function forgotPassword(ForgotPasswordRequest $request, AuditLogger $auditLogger): JsonResponse
    {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();

        $key = 'auth-forgot:'.sha1($phone.'|'.$request->ip());
        $ipKey = 'auth-forgot-ip:'.sha1((string) $request->ip());

        if (RateLimiter::tooManyAttempts($key, self::FORGOT_MAX_ATTEMPTS)
            || RateLimiter::tooManyAttempts($ipKey, self::FORGOT_IP_MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'phone' => ['Too many requests. Please try again later.'],
            ]);
        }

        RateLimiter::hit($key, self::FORGOT_WINDOW_SECONDS);
        RateLimiter::hit($ipKey, self::FORGOT_WINDOW_SECONDS);

        $user = User::query()->wherePhone($phone)->first();

        if ($user !== null && $user->isActive() && $user->email !== null) {
            $token = bin2hex(random_bytes(32));

            // The plaintext token only ever exists in the emailed link; this
            // row stores its sha256 plus a single-use expiry window.
            $user->forceFill([
                'reset_token_hash' => hash('sha256', $token),
                'reset_expires_at' => now()->addMinutes(self::RESET_TOKEN_TTL_MINUTES),
            ])->save();

            try {
                Mail::to($user->email)->send(
                    new ResetPasswordLink($user->name, $token, self::RESET_TOKEN_TTL_MINUTES),
                );
            } catch (\Throwable $exception) {
                // Transport problems must not change the response: the same
                // "if this account has an email" message goes out either way.
                report($exception);
            }

            $auditLogger->log($request, 'auth.password_forgot', $user, ['user_id' => $user->id]);
        }

        return response()->json([
            'message' => 'If this account has an email address on file, a password reset link has been sent. '
                .'Check your inbox and spam folder. Accounts without an email address on file need '
                .'support to set a password.',
        ]);
    }

    /**
     * Step 2 of recovery: consume the emailed token and set a new password.
     *
     * On success every existing session is revoked (a reset usually means "I
     * lost control of this account") and the requester is signed in fresh, so
     * the customer lands back in their account with no further steps.
     */
    public function resetPassword(
        ResetPasswordRequest $request,
        IssueSanctumToken $tokenIssuer,
        AuditLogger $auditLogger,
    ): JsonResponse {
        $key = 'auth-reset-ip:'.sha1((string) $request->ip());

        if (RateLimiter::tooManyAttempts($key, self::RESET_MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'token' => ['Too many attempts. Please try again later.'],
            ]);
        }

        RateLimiter::hit($key, self::RESET_WINDOW_SECONDS);

        $hash = hash('sha256', $request->string('token')->toString());

        $user = User::query()
            ->where('reset_token_hash', $hash)
            ->whereNotNull('reset_expires_at')
            ->where('reset_expires_at', '>', now())
            ->first();

        if ($user === null || ! $user->isActive()) {
            throw ValidationException::withMessages([
                'token' => ['This password reset link is invalid or has expired.'],
            ]);
        }

        $user = DB::transaction(function () use ($user, $hash, $request): User {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->first();

            // Re-check under the row lock: a parallel request may have already
            // consumed this token between the lookup above and here.
            if ($locked === null
                || ! $locked->isActive()
                || $locked->reset_token_hash === null
                || ! hash_equals($locked->reset_token_hash, $hash)
                || $locked->reset_expires_at === null
                || $locked->reset_expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'token' => ['This password reset link is invalid or has expired.'],
                ]);
            }

            // password / reset_token_hash are force-filled: not mass
            // assignable, and the hashed cast bcrypts the plaintext on save.
            $locked->forceFill([
                'password' => $request->string('password')->toString(),
                'reset_token_hash' => null,
                'reset_expires_at' => null,
            ])->save();

            $locked->tokens()->delete();

            return $locked;
        });

        $auditLogger->log($request, 'auth.password_reset', $user, ['user_id' => $user->id], $user);

        $token = $tokenIssuer->execute($user);

        return $this->withWebSessionCookie(response()->json([
            'message' => 'Password updated.',
            'user' => new UserResource($user),
        ]), $token);
    }

    /**
     * Guest checkout, one step: phone + password (+ name when the number is
     * new). Replaces the old SMS code exchange — no verification code exists
     * anywhere in this flow. Known numbers are verified against the same
     * lockout budget as /auth/login; new numbers create a lightweight account
     * (name + phone + password) so order ownership, status polling, repay and
     * My orders work unchanged. `registered` tells the UI which path ran.
     */
    public function checkoutSignin(
        CheckoutSigninRequest $request,
        RegisterUser $registerUser,
        IssueSanctumToken $tokenIssuer,
        AuditLogger $auditLogger,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();
        $password = $request->string('password')->toString();

        $existing = User::query()->wherePhone($phone)->first();

        if ($existing !== null) {
            if (! $existing->isActive()) {
                throw ValidationException::withMessages([
                    'phone' => ['Unable to authenticate this account.'],
                ]);
            }

            $key = $this->loginRateKey($phone, (string) $request->ip());

            if (RateLimiter::tooManyAttempts($key, self::LOGIN_MAX_ATTEMPTS)) {
                throw ValidationException::withMessages([
                    'phone' => ['Too many attempts. Please try again in a few minutes.'],
                ]);
            }

            if ($existing->password === null) {
                RateLimiter::hit($key, self::LOGIN_LOCKOUT_SECONDS);

                /*
                 * Legacy accounts created in the SMS-code era have no password.
                 * They cannot be claimed here (that would let anyone who knows
                 * the number take over), so point at the reset flow instead of
                 * failing with a mysterious "incorrect password". This is the
                 * one deliberate account-existence exception, bounded by the
                 * lockout above.
                 */
                throw ValidationException::withMessages([
                    'phone' => ['This account does not have a password yet. Use "Forgot password" on the sign-in page to set one, then come back.'],
                ]);
            }

            if (! Hash::check($password, (string) $existing->password)) {
                RateLimiter::hit($key, self::LOGIN_LOCKOUT_SECONDS);

                throw ValidationException::withMessages([
                    'phone' => ['The phone number or password you entered is incorrect.'],
                ]);
            }

            RateLimiter::clear($key);

            $token = $this->issueSession($existing, $tokenIssuer);

            $auditLogger->log($request, 'auth.login', $existing, ['user_id' => $existing->id, 'via' => 'checkout'], $existing);

            return $this->withWebSessionCookie(response()->json([
                'message' => 'Login successful.',
                'user' => new UserResource($existing),
                'registered' => false,
            ]), $token);
        }

        // Brand-new account at checkout: same anti-farm budget as /auth/register.
        $this->guardSignup($request);

        $name = $request->string('name')->trim()->toString();

        if (mb_strlen($name) < 2) {
            throw ValidationException::withMessages([
                'name' => ['Please tell us your name to create your account.'],
            ]);
        }

        // Race: the number registered between lookup and here (another tab).
        // createAccount() maps the unique-index violation to a clean error.
        $user = $this->createAccount($registerUser, new RegisterUserData(
            name: $name,
            phone: $phone,
            referralCode: null,
            password: $password,
            email: null,
            deviceHash: DeviceIdentity::hash(DeviceIdentity::id($request)),
            signupIp: $request->ip(),
        ));

        $token = $tokenIssuer->execute($user);

        $auditLogger->log($request, 'auth.registered', $user, ['user_id' => $user->id, 'via' => 'checkout'], $user);

        return $this->withWebSessionCookie(response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
            'registered' => true,
        ], 201), $token);
    }

    public function logout(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $plainTextToken = $request->bearerToken();

        if ($plainTextToken !== null) {
            $auditLogger->log($request, 'auth.logout', $request->user());
            PersonalAccessToken::findToken($plainTextToken)?->delete();
        }

        /*
         * Sanctum's RequestGuard can be cached by Laravel's AuthManager for
         * the lifetime of the application/test process. After revoking an
         * API token, clear cached guards so a subsequent request must perform
         * fresh bearer-token authentication against the database.
         */
        Auth::forgetGuards();

        return $this->withoutWebSessionCookie(response()->json([
            'message' => 'Logout successful.',
        ]));
    }

    /** Sign the user out everywhere: revokes every token belonging to the account. */
    public function logoutAll(Request $request, AuditLogger $auditLogger): JsonResponse
    {
        $user = $request->user();

        $auditLogger->log($request, 'auth.logout_all', $user);
        $user->tokens()->delete();

        Auth::forgetGuards();

        return $this->withoutWebSessionCookie(response()->json([
            'message' => 'Signed out of all devices.',
        ]));
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    private function canPasswordAuthenticate(?User $user, string $password): bool
    {
        return $user !== null
            && $user->isActive()
            && $user->password !== null
            && Hash::check($password, (string) $user->password);
    }

    /**
     * Anti-farm cap on account creation: per connection (loose — mobile
     * carriers put many real customers behind one public IP) and per device
     * cookie (tight — unique per browser). Attempt-based and run before any
     * database work so the counters are never rolled back with a transaction.
     */
    private function guardSignup(Request $request): void
    {
        $ip = sha1((string) $request->ip());
        $device = DeviceIdentity::id($request);

        $limits = [
            'auth-signup-ip-m:'.$ip => [(int) config('security.signup_ip_per_minute'), 60],
            'auth-signup-ip-h:'.$ip => [(int) config('security.signup_ip_per_hour'), 3600],
            'auth-signup-ip-d:'.$ip => [(int) config('security.signup_ip_per_day'), 86400],
        ];

        if ($device !== null) {
            $limits['auth-signup-dev-d:'.sha1($device)] = [(int) config('security.signup_device_per_day'), 86400];
        }

        foreach ($limits as $key => [$max, $decay]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw ValidationException::withMessages([
                    'phone' => ['Too many accounts have been created from this connection. Please try again later.'],
                ]);
            }
        }

        foreach ($limits as $key => [$max, $decay]) {
            RateLimiter::hit($key, $decay);
        }
    }

    private function loginRateKey(string $phone, string $ip): string
    {
        return 'auth-login:'.sha1($phone.'|'.$ip);
    }

    /**
     * Create the account, mapping a unique blind-index collision (the number
     * or email registered in the race window after our existence checks) to a
     * clean validation error instead of a 500.
     */
    private function createAccount(RegisterUser $registerUser, RegisterUserData $data): User
    {
        try {
            return $registerUser->execute($data);
        } catch (QueryException $exception) {
            if (! ($exception instanceof \Illuminate\Database\UniqueConstraintViolationException)) {
                throw $exception;
            }
            $emailRace = str_contains($exception->getMessage(), 'email_hash');

            throw ValidationException::withMessages([
                $emailRace ? 'email' : 'phone' => [$emailRace
                    ? 'This email address is already in use.'
                    : 'This phone number is already registered. Sign in instead.'],
            ]);
        }
    }

    /**
     * Revoke every existing session and mint a fresh token under a row lock,
     * so concurrent sign-ins cannot interleave revocation with issuance.
     */
    private function issueSession(User $user, IssueSanctumToken $tokenIssuer): string
    {
        return DB::transaction(function () use ($user, $tokenIssuer): string {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->first();

            if (! $locked || ! $locked->isActive()) {
                throw ValidationException::withMessages([
                    'phone' => ['Unable to authenticate this account.'],
                ]);
            }

            $locked->tokens()->delete();

            return $tokenIssuer->execute($locked);
        });
    }

    private function withWebSessionCookie(JsonResponse $response, string $plainTextToken): JsonResponse
    {
        return $response->withCookie(
            Cookie::make(
                config('web_session.cookie'),
                $plainTextToken,
                (int) (config('sanctum.expiration') ?? 43200),
                config('web_session.path'),
                config('web_session.domain'),
                config('web_session.secure'),
                config('web_session.http_only'),
                false,
                config('web_session.same_site'),
            ),
        );
    }

    private function withoutWebSessionCookie(JsonResponse $response): JsonResponse
    {
        return $response->withCookie(
            Cookie::forget(config('web_session.cookie'), config('web_session.path'), config('web_session.domain')),
        );
    }
}
