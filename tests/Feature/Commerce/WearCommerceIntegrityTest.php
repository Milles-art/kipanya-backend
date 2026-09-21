<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Models\Commerce\Address;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Wear\WearInventoryMovement;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WearCommerceIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['status' => UserStatus::Active, 'phone_verified_at' => now()]);
    }

    private function product(int $stock = 5): WearProductVariant
    {
        $product = WearProduct::create([
            'name' => 'Integrity Tee', 'slug' => 'integrity-'.uniqid(), 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        return WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => $stock, 'sku' => 'INT-'.strtoupper(bin2hex(random_bytes(4))),
        ]);
    }

    private function address(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id, 'type' => 'shipping', 'recipient_name' => 'Customer',
            'phone' => '+255712345678', 'region' => 'Dar es Salaam', 'district' => 'Kinondoni',
            'ward' => 'Mwananyamala', 'street' => 'Kipanya Street', 'is_default' => true,
        ]);
    }

    public function test_failed_payment_releases_reservation_and_cancels_pending_order(): void
    {
        $user = $this->user();
        $variant = $this->product(3);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'integrity-failed-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();

        $payment = PaymentTransaction::findOrFail($response->json('data.payment.0.id'));
        app(PaymentService::class)->markFailed($payment, ['reason' => 'declined']);

        $this->assertDatabaseHas('payment_transactions', ['id' => $payment->id, 'status' => 'failed']);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'cancelled', 'payment_status' => 'failed']);
        $this->assertDatabaseHas('wear_stock_reservations', ['wear_order_id' => $payment->wear_order_id, 'status' => 'released']);
        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'stock' => 3]);
        $this->assertDatabaseHas('wear_inventory_movements', ['wear_product_variant_id' => $variant->id, 'quantity' => 0, 'reason' => 'Reservation released']);
    }

    public function test_successful_payment_creates_a_sale_inventory_movement(): void
    {
        $user = $this->user();
        $variant = $this->product(3);
        $address = $this->address($user);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'integrity-sale-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();

        $payment = PaymentTransaction::findOrFail($response->json('data.payment.0.id'));
        app(PaymentService::class)->markSuccessful($payment, ['fake' => true]);

        $this->assertDatabaseHas('wear_inventory_movements', [
            'wear_product_variant_id' => $variant->id, 'quantity' => -2, 'stock_before' => 3,
            'stock_after' => 1, 'reason' => 'Sale',
        ]);
    }

    public function test_paid_order_cannot_be_cancelled_as_an_order_state_change(): void
    {
        $user = $this->user();
        $variant = $this->product(2);
        $address = $this->address($user);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'integrity-paid-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();
        $payment = PaymentTransaction::findOrFail($response->json('data.payment.0.id'));
        app(PaymentService::class)->markSuccessful($payment, ['fake' => true]);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders/'.$response->json('data.order_number').'/cancel')
            ->assertUnprocessable();

        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'confirmed', 'payment_status' => 'paid']);
    }
    public function test_expired_reservation_rejects_late_payment_and_marks_reconciliation_required(): void
    {
        $user = $this->user();
        $variant = $this->product(2);
        $address = $this->address($user);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'integrity-expired-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();
        $payment = PaymentTransaction::findOrFail($response->json('data.payment.0.id'));
        $payment->order->stockReservation()->update(['expires_at' => now()->subMinute()]);

        $result = app(PaymentService::class)->markSuccessful($payment, ['fake' => true]);
        $this->assertSame('failed', $result->status->value);

        $this->assertDatabaseHas('payment_transactions', ['id' => $payment->id, 'status' => 'failed']);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'cancelled', 'payment_status' => 'failed']);
    }

}
