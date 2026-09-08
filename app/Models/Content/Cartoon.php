<?php

namespace App\Models\Content;

use App\Enums\Content\ContentStatus;
use App\Models\Wear\WearDesign;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Cartoon extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'caption',
        'thumbnail_url',
        'thumbnail_path',
        'artwork_format',
        'status',
        'is_featured',
        'is_daily',
        'daily_date',
        'published_at',
        'sort_order',
        'shares_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_featured' => 'boolean',
            'is_daily' => 'boolean',
            'daily_date' => 'date',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
            'shares_count' => 'integer',
            'artwork_format' => 'string',
        ];
    }

    public function getResolvedThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }

        return $this->thumbnail_url;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(CartoonEpisode::class)->orderBy('episode_number');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CartoonLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CartoonComment::class);
    }

    public function wearDesigns(): HasMany
    {
        return $this->hasMany(WearDesign::class);
    }

}
