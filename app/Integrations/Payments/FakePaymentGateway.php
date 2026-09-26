<?php

namespace App\Integrations\Payments;

use App\Models\Wear\WearOrder;
use Illuminate\Support\Str;
use RuntimeException;

final class FakePaymentGateway implements PaymentGateway
{
    public function initiate(WearOrder $order, string $idempotencyKey): array
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'FakePaymentGateway cannot be used in production. Bind a real PaymentGateway implementation.'
            );
        }

        return [
            'provider' => 'fake',
            'reference' => 'FAKE-'.Str::upper(Str::random(18)),
            'status' => 'pending',
            'payload' => [
                'environment' => app()->environment(),
                'order_number' => $order->order_number,
                'idempotency_key' => $idempotencyKey,
                // Local stand-in for Selcom's hosted payment page: the simulator
                // below lets developers complete or fail the payment end to
                // end. Never present in production (this gateway refuses to
                // boot there).
                'payment_gateway_url' => url('/pay/simulate/'.$order->order_number),
            ],
        ];
    }

    public function status(string $orderId): array
    {
        return [
            'status' => 'pending',
            'provider' => 'fake',
            'payload' => [
                'order_id' => $orderId,
            ],
        ];
    }

    public function cancel(string $orderId): bool
    {
        return true;
    }
}
