<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Enums\Auth\OtpPurpose;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\ValidTanzanianPhoneNumber;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ProfileContactController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    /**
     * Step 1 of a phone change: validate the new number and send a
     * verification code to it.
     */
    public function requestPhoneChange(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', new ValidTanzanianPhoneNumber],
        ]);

        $phone = PhoneNumber::normalize($data['phone'])->value();
        $user = $request->user();

        if ($user->phone !== null && hash_equals((string) $user->phone, $phone)) {
            throw ValidationException::withMessages([
                'phone' => 'This is already your phone number.',
            ]);
        }

        if (User::query()->wherePhone($phone)->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'This phone number is already in use.',
            ]);
        }

        $this->otp->send($phone, OtpPurpose::PhoneChange);

        return response()->json([
            'message' => 'A verification code has been sent to the new number.',
            'data' => ['phone' => $phone],
        ]);
    }

    /**
     * Step 2 of a phone change: verify the code, then claim the number.
     *
     * Verification runs before the write transaction on purpose: recording a
     * failed attempt must persist, and OtpService documents that verify()
     * must never run inside an enclosing transaction.
     */
    public function verifyPhoneChange(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', new ValidTanzanianPhoneNumber],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $phone = PhoneNumber::normalize($data['phone'])->value();
        $user = $request->user();

        $this->otp->verify($phone, OtpPurpose::PhoneChange, $data['code']);

        $fresh = DB::transaction(function () use ($user, $phone) {
            $model = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (User::query()->wherePhone($phone)->whereKeyNot($model->id)->exists()) {
                throw ValidationException::withMessages([
                    'phone' => 'This phone number was just claimed by another account.',
                ]);
            }

            // phone_hash is maintained by the model's saving hook.
            $model->forceFill([
                'phone' => $phone,
                'phone_verified_at' => now(),
            ])->save();

            return $model->fresh();
        });

        return response()->json(['data' => [
            'id' => $fresh->id,
            'name' => $fresh->name,
            'phone' => $fresh->phone,
        ]]);
    }

    /**
     * Step 1 of an email change: validate the new address and send a
     * verification code to the account's already-verified phone number.
     */
    public function requestEmailChange(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));
        $user = $request->user();

        if ($user->phone_verified_at === null) {
            throw ValidationException::withMessages([
                'email' => 'Verify your phone number before changing your email address.',
            ]);
        }

        if ($user->email !== null && hash_equals(strtolower((string) $user->email), $email)) {
            throw ValidationException::withMessages([
                'email' => 'This is already your email address.',
            ]);
        }

        if (User::query()->whereEmail($email)->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already in use.',
            ]);
        }

        $this->otp->send((string) $user->phone, OtpPurpose::EmailChange);

        return response()->json([
            'message' => 'A verification code has been sent to your phone.',
            'data' => ['email' => $email],
        ]);
    }

    /**
     * Step 2 of an email change: verify the phone code, then claim the address.
     */
    public function verifyEmailChange(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($data['email']));
        $user = $request->user();

        if ($user->phone === null) {
            throw ValidationException::withMessages([
                'email' => 'Verify your phone number before changing your email address.',
            ]);
        }

        $this->otp->verify((string) $user->phone, OtpPurpose::EmailChange, $data['code']);

        $fresh = DB::transaction(function () use ($user, $email) {
            $model = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (User::query()->whereEmail($email)->whereKeyNot($model->id)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This email address was just claimed by another account.',
                ]);
            }

            // email_hash is maintained by the model's saving hook.
            $model->forceFill(['email' => $email])->save();

            return $model->fresh();
        });

        return response()->json(['data' => [
            'id' => $fresh->id,
            'name' => $fresh->name,
            'email' => $fresh->email,
        ]]);
    }
}
