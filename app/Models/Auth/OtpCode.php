<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code_hash',
        'purpose',
        'attempts',
        'expires_at',
        'verified_at',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'last_sent_at' => 'datetime',
            // OTP phone numbers are ciphertext at rest. The `encrypted` cast
            // is non-deterministic, so lookups go through the deterministic
            // phone_hash blind index (HMAC-SHA256 keyed with APP_KEY).
            'phone' => 'encrypted',
        ];
    }

    /**
     * Keep the blind index in step with the encrypted column on every write.
     */
    protected static function booted(): void
    {
        static::saving(function (self $otp): void {
            if ($otp->isDirty('phone')) {
                $plain = $otp->phone;

                $otp->phone_hash = $plain === null ? null : hash_hmac('sha256', (string) $plain, (string) config('app.key'));
            }
        });
    }

    public static function phoneHash(string $phone): string
    {
        return hash_hmac('sha256', $phone, (string) config('app.key'));
    }

    public function scopeWherePhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone_hash', self::phoneHash($phone));
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasExceededAttempts(int $maximum = 5): bool
    {
        return $this->attempts >= $maximum;
    }
}
