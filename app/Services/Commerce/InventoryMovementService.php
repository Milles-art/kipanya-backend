<?php

namespace App\Services\Commerce;

use App\Models\User;
use App\Models\Wear\WearInventoryMovement;
use App\Models\Wear\WearProductVariant;

final class InventoryMovementService
{
    public function record(WearProductVariant $variant, int $quantity, int $stockBefore, int $stockAfter, string $reason, ?string $notes = null, ?User $actor = null): WearInventoryMovement
    {
        return WearInventoryMovement::query()->create([
            'wear_product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reason' => mb_substr(trim($reason), 0, 60),
            'notes' => $notes,
            'created_by' => $actor?->id,
        ]);
    }
}
