<?php

namespace App\Services\Auth;

use App\Enums\Auth\OtpPurpose;
use App\Integrations\Sms\SmsGateway;
use App\Models\Auth\OtpCode;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

final class OtpService
{
    private ?string $lastPlainCode = null;

    public const TTL_MINUTES = 5;
    public const MAX_ATTEMPTS = 5;
    public const RESEND_COOLDOWN_SECONDS = 60;
    public const MAX_VERIFY_FAILURES = 10;
    public const VERIFY_LOCKOUT_SECONDS = 900;

    public function __construct(private readonly SmsGateway $sms) {}

    public function send(string $phone, OtpPurpose $purpose): OtpCode
    {
        $normalized = PhoneNumber::normalize($phone)->value();

        $latest = OtpCode::query()
            ->where('phone', $normalized)
            ->where('purpose', $purpose->value)
            ->latest('id')
            ->first();

        if ($latest?->last_sent_at?->gt(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
            throw ValidationException::withMessages([
                'phone' => ['Please wait before requesting another code.'],
            ]);
        }

        $plainCode = (string) random_int(100000, 999999);
        $this->lastPlainCode = app()->environment(['local', 'testing']) && config('auth.expose_otp_codes', false) ? $plainCode : null;

        $otp = DB::transaction(function () use ($normalized, $purpose, $plainCode) {
            OtpCode::query()
                ->where('phone', $normalized)
                ->where('purpose', $purpose->value)
                ->whereNull('verified_at')
                ->update(['verified_at' => now()]);

            return OtpCode::create([
                'phone' => $normalized,
                'code_hash' => Hash::make($plainCode),
                'purpose' => $purpose->value,
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::TTL_MINUTES),
                'last_sent_at' => now(),
            ]);
        });

        $this->sms->send(
            $normalized,
            "Your Kipanya verification code is {$plainCode}. It expires in " . self::TTL_MINUTES . " minutes."
        );

        // Never expose OTPs in production logs. This is intentionally limited to
        // local development so the Blade client login can be tested without SMS.
        if (app()->environment(['local', 'testing']) && config('auth.log_otp_codes', false)) {
            Log::info('Kipanya OTP generated for local testing', [
                'phone' => $normalized,
                'purpose' => $purpose->value,
                'code' => $plainCode,
                'expires_at' => $otp->expires_at?->toIso8601String(),
            ]);
        }

        return $otp;
    }


    public function lastPlainCode(): ?string
    {
        return $this->lastPlainCode;
    }

    public function verify(string $phone, OtpPurpose $purpose, string $code): OtpCode
    {
        $normalized = PhoneNumber::normalize($phone)->value();
        $lockoutKey = $this->verifyLockoutKey($normalized, $purpose);

        // This counter is independent of any single OTP row, so requesting a
        // fresh code cannot reset an attacker's brute-force attempt budget.
        if (RateLimiter::tooManyAttempts($lockoutKey, self::MAX_VERIFY_FAILURES)) {
            throw ValidationException::withMessages([
                'code' => ['Too many incorrect attempts. Please try again later.'],
            ]);
        }

        $result = DB::transaction(function () use ($normalized, $purpose, $code): array {
            $otp = OtpCode::query()
                ->where('phone', $normalized)
                ->where('purpose', $purpose->value)
                ->whereNull('verified_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$otp || $otp->isExpired() || $otp->hasExceededAttempts()) {
                return ['otp' => null, 'error' => true];
            }

            if (!Hash::check($code, $otp->code_hash)) {
                $otp->increment('attempts');

                return ['otp' => null, 'error' => true];
            }

            $otp->forceFill(['verified_at' => now()])->save();

            return ['otp' => $otp->fresh(), 'error' => false];
        });

        if ($result['error']) {
            RateLimiter::hit($lockoutKey, self::VERIFY_LOCKOUT_SECONDS);

            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or expired.'],
            ]);
        }

        RateLimiter::clear($lockoutKey);

        /** @var OtpCode $otp */
        return $result['otp'];
    }

    private function verifyLockoutKey(string $normalizedPhone, OtpPurpose $purpose): string
    {
        return sprintf('otp-verify:%s:%s', $purpose->value, sha1($normalizedPhone));
    }
}
