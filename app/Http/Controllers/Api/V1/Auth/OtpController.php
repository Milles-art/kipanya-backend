<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Auth\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RequestOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Services\Auth\OtpService;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function send(RequestOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $otpService->send(
            $request->string('phone')->toString(),
            OtpPurpose::from($request->string('purpose')->toString())
        );

        return response()->json([
            'message' => 'If the request is valid, a verification code has been sent.',
        ]);
    }

    public function verify(VerifyOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $otpService->verify(
            $request->string('phone')->toString(),
            OtpPurpose::from($request->string('purpose')->toString()),
            $request->string('code')->toString()
        );

        return response()->json([
            'message' => 'Verification successful.',
        ]);
    }
}
