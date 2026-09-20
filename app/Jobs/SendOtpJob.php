<?php

namespace App\Jobs;

use App\Enums\Auth\OtpPurpose;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Sends an OTP only when the account is eligible, and never reveals that fact to
 * the requester. It is dispatched with ->afterResponse() so the HTTP response is
 * identical (body, status and timing) for known and unknown phone numbers, and so
 * rate-limit/cooldown errors raised by OtpService::send() cannot be observed.
 */
final class SendOtpJob
{
    use Dispatchable;

    public function __construct(
        public readonly string $phone,
        public readonly OtpPurpose $purpose,
    ) {}

    public function handle(OtpService $otp): void
    {
        if (! $this->eligible()) {
            return;
        }

        try {
            $otp->send($this->phone, $this->purpose);
        } catch (\Throwable $e) {
            // Cooldown, per-phone limit or SMS provider failure: log, never surface.
            report($e);
        }
    }

    private function eligible(): bool
    {
        return match ($this->purpose) {
            OtpPurpose::Registration => ! User::query()->where('phone', $this->phone)->exists(),
            OtpPurpose::Login => User::query()->where('phone', $this->phone)->where('status', 'active')->exists(),
            OtpPurpose::AdminLogin => User::query()->where('phone', $this->phone)->where('status', 'active')->get()
                ->contains(fn (User $user): bool => $user->isAdmin()),
            default => false,
        };
    }
}
