<?php

namespace App\DTOs\Commerce;

final readonly class CreateWearOrderData
{
    public function __construct(
        public int $addressId,
        public ?string $notes,
        public string $idempotencyKey,
        public ?string $paymentMethod = null,
        public ?string $paymentProvider = null,
        public ?string $paymentPhone = null,
    ) {}
}
