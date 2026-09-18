<?php

namespace App\Integrations\Payments;

use App\Models\Wear\WearOrder;

interface PaymentGateway
{
    /** @return array{provider:string, reference:string, payload:array, status:string} */
    public function initiate(WearOrder $order, string $idempotencyKey): array;

    /**
     * Query the provider for the current state of an initiated order.
     *
     * @return array{status:string, provider:string, transid?:string|null, reference?:string|null, channel?:string|null, amount?:string|int|null, payload?:array}
     */
    public function status(string $orderId): array;

    public function cancel(string $orderId): bool;
}
