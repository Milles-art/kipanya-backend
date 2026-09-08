<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Auth\OtpPurpose;
use App\Enums\Auth\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\RequestLoginOtpRequest;
use App\Http\Requests\Api\V1\Auth\RequestRegistrationOtpRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Actions\Auth\IssueSanctumToken;
use App\Actions\Auth\RegisterUser;
use App\DTOs\Auth\RegisterUserData;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (User::query()->where('phone', $phone)->exists()) {
            return response()->json([
                'message' => 'If the request is valid, a verification code has been sent.',
            ]);
        }

        $otpService->send($phone, OtpPurpose::Registration);

        return response()->json([
            'message' => 'If the request is valid, a verification code has been sent.',
        ]);
    }

    public function register(
        RegisterRequest $request,
        OtpService $otpService,
        RegisterUser $registerUser,
        IssueSanctumToken $tokenIssuer,
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

        $token = $tokenIssuer->execute($user, 'api-client');

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function requestLoginOtp(
        RequestLoginOtpRequest $request,
        OtpService $otpService,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();

        $this->limitOtpRequest('login', $phone);

        if (!User::query()->where('phone', $phone)->where('status', UserStatus::Active->value)->exists()) {
            return response()->json([
                'message' => 'If the request is valid, a verification code has been sent.',
            ]);
        }

        $otpService->send($phone, OtpPurpose::Login);

        return response()->json([
            'message' => 'If the request is valid, a verification code has been sent.',
        ]);
    }

    public function login(
        LoginRequest $request,
        OtpService $otpService,
        IssueSanctumToken $tokenIssuer,
    ): JsonResponse {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();

        $user = DB::transaction(function () use ($request, $otpService, $phone): User {
            $otpService->verify(
                $phone,
                OtpPurpose::Login,
                $request->string('code')->toString(),
            );

            $user = User::query()->where('phone', $phone)->lockForUpdate()->first();

            if (!$user || !$user->isActive()) {
                throw ValidationException::withMessages([
                    'phone' => ['Unable to authenticate this account.'],
                ]);
            }

            return $user;
        });

        $user->tokens()->delete();
        $token = $tokenIssuer->execute($user, 'api-client');

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $plainTextToken = $request->bearerToken();

        if ($plainTextToken !== null) {
            PersonalAccessToken::findToken($plainTextToken)?->delete();
        }

        /*
         * Sanctum's RequestGuard can be cached by Laravel's AuthManager for
         * the lifetime of the application/test process. After revoking an
         * API token, clear cached guards so a subsequent request must perform
         * fresh bearer-token authentication against the database.
         */
        Auth::forgetGuards();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
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
}
