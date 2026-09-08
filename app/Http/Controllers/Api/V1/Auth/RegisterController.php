<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Auth\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController extends Controller
{
    public function requestOtp(RegisterRequest $request, OtpService $otpService): JsonResponse
    {
        $phone = PhoneNumber::normalize($request->string('phone')->toString())->value();
        $key = 'register-otp:' . sha1($phone);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($key, 3600);

        $otpService->send($phone, OtpPurpose::Registration);

        return response()->json([
            'message' => 'If the request is valid, a verification code has been sent.',
        ]);
    }
}
