<?php

namespace App\Models\Wear;

use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WearOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wear_order_id', 'wear_product_id', 'wear_product_variant_id', 'product_name',
        'sku', 'size', 'color', 'quantity', 'unit_price', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(WearOrder::class, 'wear_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(WearProduct::class, 'wear_product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(WearProductVariant::class, 'wear_product_variant_id');
    }
}
