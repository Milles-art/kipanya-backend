<?php

namespace App\Models\Auth;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserProfile extends Model
{
    protected $fillable = ['user_id', 'username', 'avatar_path', 'interests', 'locale', 'timezone', 'size_profile'];

    protected function casts(): array
    {
        return ['interests' => 'array', 'size_profile' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
