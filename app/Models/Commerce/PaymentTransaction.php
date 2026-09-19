<?php

namespace App\Models\Commerce;

use App\Enums\Commerce\PaymentStatus;
use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'wear_order_id', 'user_id', 'provider', 'provider_reference',
        'provider_transid', 'idempotency_key', 'amount', 'refunded_amount',
        'currency', 'status', 'payload', 'initiated_at', 'completed_at', 'failed_at',
    ];

    protected $hidden = ['payload'];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'payload' => 'array',
            'initiated_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(WearOrder::class, 'wear_order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
