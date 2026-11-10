<?php

namespace App\Models\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SocialAccount extends Model
{
    protected $fillable = ['user_id', 'provider', 'subject_hash'];
    protected $hidden = ['subject_hash'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
