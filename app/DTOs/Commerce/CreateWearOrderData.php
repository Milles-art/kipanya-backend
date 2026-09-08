<?php

namespace App\DTOs\Commerce;

final readonly class CreateWearOrderData
{
    public function __construct(
        public int $addressId,
        public ?string $notes,
        public string $idempotencyKey,
    ) {}
}
