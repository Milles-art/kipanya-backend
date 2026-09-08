<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserProfile extends Model
{
    protected $fillable = ['user_id', 'username', 'avatar_path', 'interests', 'locale', 'timezone'];

    protected function casts(): array
    {
        return ['interests' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
