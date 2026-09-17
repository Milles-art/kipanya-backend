<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Models\Commerce\Address;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\Wear\WearInventoryMovement;
use App\Models\Commerce\StockReservation;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WearOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'status' => UserStatus::Active,
            'phone_verified_at' => now(),
        ]);
    }

    private function product(int $stock = 5): array
    {
        $product = WearProduct::create([
            'name' => 'Commerce Tee', 'slug' => 'commerce-tee-' . uniqid(), 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => $stock, 'sku' => 'COM-' . strtoupper(bin2hex(random_bytes(4))),
        ]);
        return [$product, $variant];
    }

    private function address(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'type' => 'shipping',
            'recipient_name' => 'Kipanya Customer',
            'phone' => '+255712345678',
            'region' => 'Dar es Salaam',
            'district' => 'Kinondoni',
            'ward' => 'Mwananyamala',
            'street' => 'Kipanya Street',
            'is_default' => true,
        ]);
    }

    public function test_checkout_creates_order_reservation_and_pending_payment(): void
    {
        $user = $this->user();
        [, $variant] = $this->product(5);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', 'checkout-00000000000001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('data.total', 50000)
            ->assertJsonPath('data.payment.0.status', 'pending');
        $this->assertDatabaseCount('wear_orders', 1);
        $this->assertDatabaseHas('wear_order_status_history', ['to_status' => 'pending_payment']);
        $this->assertDatabaseHas('wear_stock_reservations', ['status' => 'active']);
        $this->assertDatabaseHas('payment_transactions', ['status' => 'pending']);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'status' => 'converted']);
        $this->assertDatabaseHas('wear_orders', ['checkout_idempotency_key' => 'checkout-00000000000001']);
    }

    public function test_user_can_checkout_again_after_a_previous_cart_was_converted(): void
    {
        $user = $this->user();
        [, $variant] = $this->product(5);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', 'checkout-repeat-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])
            ->assertOk();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', 'checkout-repeat-0002')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])
            ->assertOk();

        $this->assertDatabaseCount('wear_orders', 2);
        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => 'converted',
        ]);
    }

    public function test_same_idempotency_key_returns_same_order(): void
    {
        $user = $this->user();
        [, $variant] = $this->product(5);
        $address = $this->address($user);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1]);

        $headers = ['Idempotency-Key' => 'checkout-00000000000002'];
        $first = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/checkout', ['address_id' => $address->id]);
        $second = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/checkout', ['address_id' => $address->id]);

        $first->assertOk();
        $second->assertOk()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertDatabaseCount('wear_orders', 1);
        $this->assertDatabaseCount('payment_transactions', 1);
        $this->assertDatabaseCount('wear_stock_reservations', 1);
    }

    public function test_second_order_cannot_reserve_already_reserved_stock(): void
    {
        $firstUser = $this->user();
        [, $variant] = $this->product(1);
        $firstAddress = $this->address($firstUser);

        $this->actingAs($firstUser, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($firstUser, 'sanctum')->withHeader('Idempotency-Key', 'checkout-00000000000003')
            ->postJson('/api/v1/checkout', ['address_id' => $firstAddress->id])->assertOk();

        $secondUser = $this->user();
        $secondAddress = $this->address($secondUser);
        $this->actingAs($secondUser, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($secondUser, 'sanctum')->withHeader('Idempotency-Key', 'checkout-00000000000004')
            ->postJson('/api/v1/checkout', ['address_id' => $secondAddress->id])
            ->assertUnprocessable();
    }

    public function test_successful_payment_fulfills_reservation_confirms_order_and_decrements_stock(): void
    {
        $user = $this->user();
        [, $variant] = $this->product(3);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'checkout-00000000000005')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();

        $paymentId = $response->json('data.payment.0.id');
        $payment = PaymentTransaction::findOrFail($paymentId);
        app(PaymentService::class)->markSuccessful($payment, ['fake' => true]);

        $this->assertDatabaseHas('payment_transactions', ['id' => $paymentId, 'status' => 'paid']);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'confirmed', 'payment_status' => 'paid']);
        $this->assertDatabaseHas('wear_stock_reservations', ['wear_order_id' => $payment->wear_order_id, 'status' => 'fulfilled']);
        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'stock' => 1]);
        $this->assertDatabaseHas('wear_inventory_movements', ['wear_product_variant_id' => $variant->id, 'quantity' => -2, 'stock_before' => 3, 'stock_after' => 1, 'reason' => 'Sale']);
    }

    public function test_pending_order_can_be_cancelled_and_releases_reservation(): void
    {
        $user = $this->user();
        [, $variant] = $this->product(3);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'checkout-00000000000006')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();

        $orderNumber = $response->json('data.order_number');
        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$orderNumber}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('wear_stock_reservations', ['status' => 'released']);
        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'stock' => 3]);
    }



    public function test_failed_payment_releases_active_reservation_and_marks_payment_failed(): void
    {
        $user = $this->user();
        [, $variant] = $this->product(3);
        $address = $this->address($user);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'checkout-failed-0001')->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();
        $payment = PaymentTransaction::findOrFail($response->json('data.payment.0.id'));
        app(PaymentService::class)->markFailed($payment, ['fake' => true]);
        $this->assertDatabaseHas('payment_transactions', ['id' => $payment->id, 'status' => 'failed']);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'payment_status' => 'failed']);
        $this->assertDatabaseHas('wear_stock_reservations', ['wear_order_id' => $payment->wear_order_id, 'status' => 'released']);
    }
}
