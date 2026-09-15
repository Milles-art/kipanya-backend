<?php

namespace App\Models\Wear;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WearProductVariant extends Model
{
    use HasFactory;
    protected $fillable = ['wear_product_id', 'size', 'color', 'stock', 'sku'];

    protected function casts(): array
    {
        return ['stock' => 'integer'];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(\App\Models\Cart\CartItem::class, 'wear_product_variant_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(\App\Models\Wear\WearOrderItem::class, 'wear_product_variant_id');
    }

    public function stockReservationItems(): HasMany
    {
        return $this->hasMany(\App\Models\Commerce\StockReservationItem::class, 'wear_product_variant_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(WearProduct::class, 'wear_product_id');
    }
}
