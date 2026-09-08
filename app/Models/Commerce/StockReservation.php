<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\ReservationStatus;
use App\Models\Wear\WearOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReservation extends Model
{
    use HasFactory;

    protected $table = 'wear_stock_reservations';

    protected $fillable = ['wear_order_id', 'status', 'expires_at', 'released_at', 'fulfilled_at'];

    protected function casts(): array
    {
        return [
            'status' => ReservationStatus::class,
            'expires_at' => 'datetime',
            'released_at' => 'datetime',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(WearOrder::class, 'wear_order_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReservationItem::class, 'reservation_id');
    }
}
