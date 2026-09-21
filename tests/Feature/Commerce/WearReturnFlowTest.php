<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Models\Commerce\Address;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Wear\WearInventoryMovement;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\Wear\WearReturnRequest;
use App\Services\Commerce\WearReturnService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WearReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(): \App\Models\User
    {
        return \App\Models\User::factory()->create(['status' => UserStatus::Active, 'phone_verified_at' => now()]);
    }

    private function deliveredOrder(\App\Models\User $user, int $stock = 1): array
    {
        $product = WearProduct::create(['name' => 'Return Tee', 'slug' => 'return-tee-'.uniqid(), 'price' => 25000, 'category' => 'shirts', 'is_active' => true]);
        $variant = WearProductVariant::create(['wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black', 'stock' => $stock, 'sku' => 'RET-'.strtoupper(bin2hex(random_bytes(4)))]);
        $address = Address::create(['user_id' => $user->id, 'type' => 'shipping', 'recipient_name' => 'Customer', 'phone' => '+255712345678', 'region' => 'Dar es Salaam', 'district' => 'Kinondoni', 'street' => 'Kipanya Street', 'is_default' => true]);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1]);
        $orderResponse = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'ret-'.uniqid())->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();
        $order = WearOrder::findOrFail($orderResponse->json('data.id'));
        $payment = PaymentTransaction::where('wear_order_id', $order->id)->firstOrFail();
        app(PaymentService::class)->markSuccessful($payment, ['fake' => true]);
        $order->update(['status' => 'delivered']);
        return [$order->fresh('items'), $variant];
    }

    public function test_processed_return_restores_stock_once_and_records_restock_movement(): void
    {
        $user = $this->user();
        [$order, $variant] = $this->deliveredOrder($user, 1);
        $item = $order->items->first();

        $request = WearReturnRequest::create(['wear_order_id' => $order->id, 'user_id' => $user->id, 'request_type' => 'return', 'reason' => 'wrong_size', 'order_item_ids' => [$item->id], 'status' => 'received']);
        app(WearReturnService::class)->process($request, $user->id);

        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'stock' => 1]);
        $this->assertDatabaseHas('wear_inventory_movements', ['wear_product_variant_id' => $variant->id, 'quantity' => 1, 'stock_before' => 0, 'stock_after' => 1, 'reason' => 'Return restock']);
        $this->assertDatabaseHas('wear_return_requests', ['id' => $request->id, 'status' => 'processed', 'refund_status' => 'pending']);
    }

    public function test_refund_is_linked_and_cannot_exceed_remaining_payment(): void
    {
        $user = $this->user();
        [$order] = $this->deliveredOrder($user, 1);
        $item = $order->items->first();
        $request = WearReturnRequest::create(['wear_order_id' => $order->id, 'user_id' => $user->id, 'request_type' => 'return', 'reason' => 'damaged', 'order_item_ids' => [$item->id], 'status' => 'processed', 'refund_amount' => $item->line_total, 'refund_status' => 'pending', 'processed_at' => now()]);

        app(WearReturnService::class)->markRefunded($request, 'REF-123', $user->id);

        $this->assertDatabaseHas('wear_return_requests', ['id' => $request->id, 'status' => 'refunded', 'refund_status' => 'completed', 'refund_reference' => 'REF-123']);
        $this->assertDatabaseHas('payment_transactions', ['wear_order_id' => $order->id, 'refunded_amount' => 25000, 'status' => 'refunded']);
        $this->assertDatabaseHas('wear_orders', ['id' => $order->id, 'payment_status' => 'refunded']);
    }

    public function test_exchange_processing_is_blocked_until_exchange_fulfillment_exists(): void
    {
        $user = $this->user();
        [$order] = $this->deliveredOrder($user, 1);
        $item = $order->items->first();
        $request = WearReturnRequest::create(['wear_order_id' => $order->id, 'user_id' => $user->id, 'request_type' => 'exchange', 'reason' => 'wrong_size', 'order_item_ids' => [$item->id], 'status' => 'received']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(WearReturnService::class)->process($request);
    }

    // ----------------------------------------------------------------- F-10

    private function pendingRefund(\App\Models\User $user): WearReturnRequest
    {
        [$order] = $this->deliveredOrder($user, 1);
        $item = $order->items->first();

        return WearReturnRequest::create(['wear_order_id' => $order->id, 'user_id' => $user->id, 'request_type' => 'return', 'reason' => 'damaged', 'order_item_ids' => [$item->id], 'status' => 'processed', 'refund_amount' => $item->line_total, 'refund_status' => 'pending', 'processed_at' => now()]);
    }

    public function test_a_refund_reference_cannot_be_reused_for_another_return(): void
    {
        $user = $this->user();
        $first = $this->pendingRefund($user);
        $second = $this->pendingRefund($user);

        app(WearReturnService::class)->markRefunded($first, 'SELCOM-REF-9001', $user->id);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        try {
            app(WearReturnService::class)->markRefunded($second, 'SELCOM-REF-9001', $user->id);
        } finally {
            $this->assertDatabaseHas('wear_return_requests', ['id' => $second->id, 'refund_status' => 'pending']);
        }
    }

    public function test_a_trivial_refund_reference_is_rejected(): void
    {
        $user = $this->user();
        $return = $this->pendingRefund($user);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(WearReturnService::class)->markRefunded($return, ' x ', $user->id);
    }
}
