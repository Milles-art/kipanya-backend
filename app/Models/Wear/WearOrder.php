<?php

namespace App\Models\Wear;

use App\Models\User;
use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Models\Commerce\OrderStatusHistory;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Commerce\StockReservation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WearOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'checkout_idempotency_key', 'checkout_fingerprint', 'user_id', 'customer_name', 'customer_phone', 'customer_email',
        'delivery_address', 'delivery_city', 'notes', 'subtotal', 'delivery_fee', 'total',
        'status', 'payment_status', 'payment_method', 'placed_at',
        'delivery_provider', 'tracking_number', 'fulfillment_notes', 'shipped_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(WearOrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'wear_order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'wear_order_id');
    }

    public function stockReservation(): HasOne
    {
        return $this->hasOne(StockReservation::class, 'wear_order_id');
    }
}
