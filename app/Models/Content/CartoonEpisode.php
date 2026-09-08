<?php

namespace App\Models\Content;

use App\Enums\Content\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartoonEpisode extends Model
{
    use HasFactory;

    protected $fillable = [
        'cartoon_id',
        'title',
        'slug',
        'description',
        'youtube_video_id',
        'youtube_url',
        'thumbnail_url',
        'duration_seconds',
        'episode_number',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'duration_seconds' => 'integer',
            'episode_number' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function cartoon(): BelongsTo
    {
        return $this->belongsTo(Cartoon::class);
    }
}
