<?php

namespace App\Http\Resources\Api\V1\Commerce;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WearProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'price' => (float) $this->price,
            'compare_at_price' => $this->compare_at_price !== null ? (float) $this->compare_at_price : null,
            'image' => $this->image_url,
            'gallery' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => $image->url,
                'role' => $image->role,
                'alt' => $image->alt_text ?: $this->name,
            ])->values()),
            'badge' => $this->badge,
            'is_featured' => (bool) $this->is_featured,
            'availability' => $this->availabilityLabel(),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'size' => $variant->size,
                'color' => $variant->color,
                'stock' => (int) $variant->stock,
                'in_stock' => (int) $variant->stock > 0,
                'sku' => $variant->sku,
            ])->values()),
        ];
    }

    private function availabilityLabel(): string
    {
        if (! $this->relationLoaded('variants')) {
            return 'available';
        }

        return $this->variants->contains(fn ($variant) => (int) $variant->stock > 0)
            ? 'in_stock'
            : 'out_of_stock';
    }
}
