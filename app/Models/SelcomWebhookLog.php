<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelcomWebhookLog extends Model
{
    protected $fillable = [
        'transid',
        'order_id',
        'payment_transaction_id',
        'payment_status',
        'processed_successfully',
        'error',
        'raw_payload',
    ];

    protected $casts = [
        'processed_successfully' => 'boolean',
        // Raw gateway payloads carry customer mobile-money numbers: ciphertext
        // at rest. Nothing queries into this column, so no blind index needed.
        'raw_payload' => 'encrypted:array',
    ];

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id');
    }
}
