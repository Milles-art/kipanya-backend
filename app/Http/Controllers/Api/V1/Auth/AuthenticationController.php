<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\IssueSanctumToken;
use App\Actions\Auth\RegisterUser;
use App\DTOs\Auth\RegisterUserData;
use App\Enums\Auth\OtpPurpose;
use App\Enums\Auth\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\RequestLoginOtpRequest;
use App\Http\Requests\Api\V1\Auth\RequestRegistrationOtpRequest;
use App\Jobs\SendOtpJob;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Support\AuditLogger;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthenticationController extends Controller
{
    public function requestRegistrationOtp(
        RequestRegistrationOtpRequest $request,
        OtpService $otpService,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();

        $this->limitOtpRequest('registration', $phone);

        // Production path: identical response for known/unknown numbers (no timing,
        // status or cooldown oracle); the SMS is sent after the response.
        if (! $this->exposesDevOtp()) {
            SendOtpJob::dispatch($phone, OtpPurpose::Registration)->afterResponse();

            return response()->json([
                'message' => 'If the request is valid, a verification code has been sent.',
            ]);
        }

        if (User::query()->where('phone', $phone)->exists()) {
            return response()->json([
                'message' => 'If the request is valid, a verification code has been sent.',
            ]);
        }

        $otpService->send($phone, OtpPurpose::Registration);

        $response = [
            'message' => 'If the request is valid, a verification code has been sent.',
        ];

        if (app()->environment(['local', 'testing']) && config('auth.expose_otp_codes', false)) {
            $response['dev_otp'] = $otpService->lastPlainCode();
        }

        return response()->json($response);
    }

    public function register(
        RegisterRequest $request,
        OtpService $otpService,
        RegisterUser $registerUser,
        IssueSanctumToken $tokenIssuer,
        AuditLogger $auditLogger,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();

        $otpService->verify(
            $phone,
            OtpPurpose::Registration,
            $request->string('code')->toString(),
        );

        if (User::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        $user = $registerUser->execute(new RegisterUserData(
            name: $request->string('name')->toString(),
            phone: $phone,
            referralCode: $request->string('referral_code')->trim()->toString() ?: null,
        ));

        $token = $tokenIssuer->execute($user);

        $auditLogger->log($request, 'auth.registered', $user, ['user_id' => $user->id], $user);

        return $this->withWebSessionCookie(response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
        ], 201), $token);
    }

    public function requestLoginOtp(
        RequestLoginOtpRequest $request,
        OtpService $otpService,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();

        $this->limitOtpRequest('login', $phone);

        if (! $this->exposesDevOtp()) {
            SendOtpJob::dispatch($phone, OtpPurpose::Login)->afterResponse();

            return response()->json([
                'message' => 'If the request is valid, a verification code has been sent.',
            ]);
        }

        if (! User::query()->where('phone', $phone)->where('status', UserStatus::Active->value)->exists()) {
            return response()->json([
                'message' => 'If the request is valid, a verification code has been sent.',
            ]);
        }

        $otpService->send($phone, OtpPurpose::Login);

        $response = [
            'message' => 'If the request is valid, a verification code has been sent.',
        ];

        if (app()->environment(['local', 'testing']) && config('auth.expose_otp_codes', false)) {
            $response['dev_otp'] = $otpService->lastPlainCode();
        }

        return response()->json($response);
    }

    public function login(
        LoginRequest $request,
        OtpService $otpService,
        IssueSanctumToken $tokenIssuer,
        AuditLogger $auditLogger,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();

        // SECURITY: verify() must NEVER run inside a database transaction. A wrong
        // code makes it throw after recording the failure (per-code attempts and
        // the lockout counter). Wrapping it in a transaction rolled those writes
        // back, silently disabling brute-force protection on this endpoint.
        $otpService->verify(
            $phone,
            OtpPurpose::Login,
            $request->string('code')->toString(),
        );

        $user = DB::transaction(function () use ($phone): User {
            $user = User::query()->where('phone', $phone)->lockForUpdate()->first();

            if (! $user || ! $user->isActive()) {
                throw ValidationException::withMessages([
                    'phone' => ['Unable to authenticate this account.'],
                ]);
            }

            $user->tokens()->delete();

            return $user;
        });

        $token = $tokenIssuer->execute($user);

        $auditLogger->log($request, 'auth.login', $user, ['user_id' => $user->id], $user);

        return $this->withWebSessionCookie(response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user),
        ]), $token);
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

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Local/testing only: return the OTP in the response for browser testing. This
     * needs the code synchronously, so it bypasses the constant-response path.
     */
    private function exposesDevOtp(): bool
    {
        return app()->environment(['local', 'testing']) && config('auth.expose_otp_codes', false);
    }

    private function limitOtpRequest(string $purpose, string $phone): void
    {
        $key = sprintf('auth-otp:%s:%s', $purpose, sha1($phone));

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'phone' => ['Too many requests. Please try again later.'],
            ]);
        }

        RateLimiter::hit($key, 3600);
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
