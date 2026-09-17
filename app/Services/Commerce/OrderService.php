<?php

namespace App\Services\Commerce;

use App\DTOs\Commerce\CreateWearOrderData;
use App\Enums\Commerce\CartStatus;
use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Cart\Cart;
use App\Models\Commerce\Address;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutPreviewService;
use App\Services\Payments\PaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class OrderService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutPreviewService $previewService,
        private readonly InventoryReservationService $inventoryReservationService,
        private readonly PaymentService $paymentService,
    ) {}

    public function create(User $user, CreateWearOrderData $data): WearOrder
    {
        return DB::transaction(function () use ($user, $data): WearOrder {
            $existing = WearOrder::query()
                ->where('checkout_idempotency_key', $data->idempotencyKey)
                ->first();

            if ($existing) {
                if ($existing->user_id !== $user->id) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'This idempotency key is already in use.',
                    ]);
                }

                return $existing->load(['items', 'payments', 'stockReservation.items.variant.product']);
            }

            $address = Address::query()
                ->where('user_id', $user->id)
                ->where('type', 'shipping')
                ->findOrFail($data->addressId);

            $cart = $this->cartService->current($user, null);
            $cart = Cart::query()->lockForUpdate()->findOrFail($cart->id);
            $cart->load('items.variant.product');

            if ($cart->status !== CartStatus::Active) {
                throw ValidationException::withMessages(['cart' => 'Your cart is no longer available for checkout.']);
            }

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $preview = $this->previewService->preview($cart);

            try {
                $order = WearOrder::create([
                    'order_number' => $this->orderNumber(),
                    'checkout_idempotency_key' => $data->idempotencyKey,
                    'user_id' => $user->id,
                    'customer_name' => $address->recipient_name,
                    'customer_phone' => $address->phone,
                    'customer_email' => $user->email,
                    'delivery_address' => implode(', ', array_filter([
                        $address->street,
                        $address->ward,
                        $address->district,
                        $address->region,
                    ])),
                    'delivery_city' => $address->district,
                    'notes' => $data->notes,
                    'subtotal' => $preview['subtotal'],
                    'delivery_fee' => $preview['delivery_fee'],
                    'total' => $preview['total'],
                    'status' => OrderStatus::PendingPayment,
                    'payment_status' => PaymentStatus::Pending,
                    'placed_at' => now(),
                ]);
            } catch (QueryException $e) {
                if (! $this->isUniqueConstraintViolation($e)) {
                    throw $e;
                }

                $existing = WearOrder::query()
                    ->where('checkout_idempotency_key', $data->idempotencyKey)
                    ->firstOrFail();

                if ($existing->user_id !== $user->id) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'This idempotency key is already in use.',
                    ]);
                }

                return $existing->load(['items', 'payments', 'stockReservation.items.variant.product']);
            }

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->variant->product;
                $unitPrice = (float) $product->price;

                $order->items()->create([
                    'wear_product_id' => $product->id,
                    'wear_product_variant_id' => $cartItem->variant->id,
                    'product_name' => $product->name,
                    'sku' => $cartItem->variant->sku,
                    'size' => $cartItem->variant->size,
                    'color' => $cartItem->variant->color,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $cartItem->quantity,
                ]);
            }

            $this->inventoryReservationService->reserve($order, $cart);
            $this->paymentService->createPending($order, $data->idempotencyKey);

            $order->statusHistory()->create([
                'from_status' => null,
                'to_status' => OrderStatus::PendingPayment->value,
                'reason' => 'Order created and inventory reserved pending payment.',
            ]);

            // A user can have only one cart per status. Converted carts are historical
            // containers; the order already stores immutable item/price snapshots, so
            // stale converted carts can be safely removed before converting the current cart.
            Cart::query()
                ->where('user_id', $user->id)
                ->where('status', CartStatus::Converted)
                ->whereKeyNot($cart->id)
                ->delete();

            $cart->update(['status' => CartStatus::Converted]);

            return $order->fresh(['items', 'payments', 'stockReservation.items.variant.product']);
        });
    }

    public function cancel(WearOrder $order, User $user): WearOrder
    {
        return DB::transaction(function () use ($order, $user): WearOrder {
            $locked = WearOrder::query()->lockForUpdate()->findOrFail($order->id);

            if ($locked->user_id !== $user->id && ! $user->hasPermission('commerce.manage')) {
                abort(403);
            }

            if ($locked->status !== OrderStatus::PendingPayment) {
                throw ValidationException::withMessages([
                    'order' => 'Only orders awaiting payment can be cancelled at this stage.',
                ]);
            }

            $locked->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Cancelled,
            ]);

            $locked->payments()
                ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Processing->value])
                ->update(['status' => PaymentStatus::Cancelled]);

            $locked->statusHistory()->create([
                'from_status' => OrderStatus::PendingPayment->value,
                'to_status' => OrderStatus::Cancelled->value,
                'reason' => 'Order cancelled before payment.',
                'changed_by' => $user->id,
            ]);

            $this->inventoryReservationService->release($locked, ReservationStatus::Released);

            return $locked->fresh(['items', 'payments', 'stockReservation.items']);
        });
    }

    private function orderNumber(): string
    {
        do {
            $candidate = 'KP-' . now()->format('ymd') . '-' . Str::upper(Str::random(8));
        } while (WearOrder::query()->where('order_number', $candidate)->exists());

        return $candidate;
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());
        return str_contains($message, '1062') || str_contains($message, 'unique constraint') || str_contains($message, 'duplicate');
    }
}
