<?php

namespace App\Models\Wear;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WearReturnRequest extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory;

    protected $fillable = [
        'wear_order_id', 'user_id', 'request_type', 'reason', 'order_item_ids', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return ['order_item_ids' => 'array'];
    }

    public function order(): BelongsTo { return $this->belongsTo(WearOrder::class, 'wear_order_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
