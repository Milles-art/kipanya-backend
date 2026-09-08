<?php

namespace App\Models\Content;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartoonComment extends Model
{
    protected $fillable = ['user_id', 'cartoon_id', 'parent_id', 'body'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cartoon(): BelongsTo { return $this->belongsTo(Cartoon::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(self::class, 'parent_id')->oldest(); }
}
