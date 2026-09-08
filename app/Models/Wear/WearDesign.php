<?php

namespace App\Models\Wear;

use App\Models\User;
use App\Models\Content\Cartoon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WearDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'cartoon_id', 'color', 'size', 'placement', 'configuration', 'status',
    ];

    protected function casts(): array
    {
        return ['configuration' => 'array'];
    }

    public function cartoon(): BelongsTo { return $this->belongsTo(Cartoon::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
