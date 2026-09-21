<?php

namespace App\Models\Wear;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WearProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'category', 'price', 'compare_at_price',
        'image_path', 'badge', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(WearCollection::class, 'wear_collection_product')
            ->withPivot('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(WearProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(WearProductImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image_path) {
            return asset('assets/wear/catalog/placeholder.svg');
        }

        if (preg_match('/^https?:\/\//i', $this->image_path)) {
            return $this->image_path;
        }

        $relative = ltrim($this->image_path, '/');

        return file_exists(public_path($relative))
            ? asset($relative)
            : asset('assets/wear/catalog/placeholder.svg');
    }
}
