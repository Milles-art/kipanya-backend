<?php

namespace App\Integrations\Payments;

use App\Models\Wear\WearOrder;

interface PaymentGateway
{
    /** @return array{provider:string,reference:string,payload:array,status:string} */
    public function initiate(WearOrder $order, string $idempotencyKey): array;
}
