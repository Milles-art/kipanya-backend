<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'payment_status' => $this->payment_status instanceof \BackedEnum ? $this->payment_status->value : $this->payment_status,
            'payment_method' => $this->payment_method,
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'total' => (float) $this->total,
            'customer' => [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
                'email' => $this->customer_email,
            ],
            'delivery' => [
                'address' => $this->delivery_address,
                'city' => $this->delivery_city,
                'provider' => $this->delivery_provider,
                'tracking_number' => $this->tracking_number,
                'shipped_at' => optional($this->shipped_at)?->toISOString(),
                'delivered_at' => optional($this->delivered_at)?->toISOString(),
            ],
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->wear_product_id,
                'variant_id' => $item->wear_product_variant_id,
                'name' => $item->product_name,
                'sku' => $item->sku,
                'size' => $item->size,
                'color' => $item->color,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ])),
            'payment' => $this->whenLoaded('payments', fn () => PaymentTransactionResource::collection($this->payments)),
            'placed_at' => optional($this->placed_at)?->toISOString(),
            'created_at' => optional($this->created_at)?->toISOString(),
        ];
    }
}
