<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'email', 'type', 'message', 'status'];

    protected function casts(): array
    {
        return [
            // Enquiry email addresses are ciphertext at rest. Lookups go
            // through the deterministic email_hash blind index.
            'email' => 'encrypted',
        ];
    }

    /**
     * Keep the blind index in step with the encrypted column on every write.
     */
    protected static function booted(): void
    {
        static::saving(function (self $message): void {
            if ($message->isDirty('email')) {
                $plain = $message->email;

                $message->email_hash = $plain === null ? null : hash_hmac('sha256', strtolower((string) $plain), (string) config('app.key'));
            }
        });
    }

    public static function emailHash(string $email): string
    {
        return hash_hmac('sha256', strtolower($email), (string) config('app.key'));
    }

    public function scopeWhereEmail(Builder $query, string $email): Builder
    {
        return $query->where('email_hash', self::emailHash($email));
    }
}
