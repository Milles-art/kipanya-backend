<?php

namespace App\Models\Wear;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

            return asset("assets/wear/catalog/generated/product-{$number}.jpg");
        }

        return asset($this->image_path);
    }
}
