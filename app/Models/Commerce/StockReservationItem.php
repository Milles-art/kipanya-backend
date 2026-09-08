<?php

namespace App\Models\Commerce;

use App\Models\Wear\WearProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservationItem extends Model
{
    use HasFactory;

    protected $table = 'wear_stock_reservation_items';

    protected $fillable = ['reservation_id', 'wear_product_variant_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(StockReservation::class, 'reservation_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(WearProductVariant::class, 'wear_product_variant_id');
    }
}
