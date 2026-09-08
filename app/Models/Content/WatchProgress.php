<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchProgress extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'cartoon_id', 'episode_id', 'progress_seconds', 'completed_at', 'last_watched_at'];

    protected function casts(): array
    {
        return ['progress_seconds' => 'integer', 'completed_at' => 'datetime', 'last_watched_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function cartoon(): BelongsTo { return $this->belongsTo(Cartoon::class); }
    public function episode(): BelongsTo { return $this->belongsTo(CartoonEpisode::class, 'episode_id'); }
}
