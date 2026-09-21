<?php

namespace App\Models\Wear;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WearReturnRequest extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = [
        'wear_order_id', 'user_id', 'request_type', 'reason', 'order_item_ids', 'notes', 'status', 'refund_amount', 'refund_status', 'refund_reference', 'approved_at', 'received_at', 'processed_at', 'refunded_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'order_item_ids' => 'array',
            'refund_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'refunded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(WearOrder::class, 'wear_order_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
