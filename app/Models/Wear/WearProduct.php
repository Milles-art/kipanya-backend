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

    public function getImageUrlAttribute(): string
    {
        if (! $this->image_path) {
            return asset('assets/wear/catalog/generated/product-01.jpg');
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (preg_match('/product-(\d+)\.jpg$/', $this->image_path, $matches)) {
            $number = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $relative = "assets/wear/catalog/generated/product-{$number}.jpg";

            return file_exists(public_path($relative))
                ? asset($relative)
                : asset('assets/wear/catalog/generated/product-01.jpg');
        }

        return file_exists(public_path($this->image_path))
            ? asset($this->image_path)
            : asset('assets/wear/catalog/generated/product-01.jpg');
    }
}
