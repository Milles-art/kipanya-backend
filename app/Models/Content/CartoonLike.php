<?php

namespace App\Models\Content;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartoonLike extends Model
{
    protected $fillable = ['user_id', 'cartoon_id'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cartoon(): BelongsTo { return $this->belongsTo(Cartoon::class); }
}
