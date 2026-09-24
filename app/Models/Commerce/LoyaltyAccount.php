<?php

namespace App\Models\Commerce;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $fillable = ['user_id', 'points', 'lifetime_points'];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'lifetime_points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'loyalty_account_id');
    }

    public function earn(int $points, ?string $description = null, array $meta = []): LoyaltyTransaction
    {
        $this->increment('points', $points);
        $this->increment('lifetime_points', $points);

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'points' => $points,
            'type' => 'earn',
            'description' => $description,
            'meta' => $meta ?: null,
        ]);
    }
}