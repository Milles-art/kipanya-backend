<?php

namespace App\Http\Resources\Api\V1\Commerce;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class WearCollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover' => $this->cover_url
                ?? ($this->relationLoaded('products') ? $this->products->first()?->image_url : null),
            'sort_order' => (int) $this->sort_order,
            'product_count' => isset($this->products_count)
                ? (int) $this->products_count
                : ($this->relationLoaded('products') ? $this->products->count() : null),
            'products' => $this->whenLoaded(
                'products',
                fn () => WearProductResource::collection($this->products)
            ),
        ];
    }
}
