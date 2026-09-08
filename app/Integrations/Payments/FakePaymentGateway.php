<?php

namespace App\Integrations\Payments;

use App\Models\Wear\WearOrder;
use Illuminate\Support\Str;

final class FakePaymentGateway implements PaymentGateway
{
    public function initiate(WearOrder $order, string $idempotencyKey): array
    {
        return [
            'provider' => 'fake',
            'reference' => 'FAKE-' . Str::upper(Str::random(18)),
            'status' => 'pending',
            'payload' => [
                'environment' => app()->environment(),
                'order_number' => $order->order_number,
                'idempotency_key' => $idempotencyKey,
            ],
        ];
    }
}
