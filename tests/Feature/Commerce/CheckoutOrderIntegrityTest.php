<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Models\Commerce\Address;
use App\Models\Commerce\PaymentTransaction;
use App\Models\Commerce\StockReservation;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CheckoutOrderIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create([
            'status' => UserStatus::Active,
            'phone_verified_at' => now(),
        ]);
    }

    private function product(int $stock = 5): WearProductVariant
    {
        $product = WearProduct::create([
            'name' => 'Integrity Tee',
            'slug' => 'integrity-'.uniqid(),
            'price' => 25000,
            'category' => 'shirts',
            'is_active' => true,
        ]);

        return WearProductVariant::create([
            'wear_product_id' => $product->id,
            'size' => 'M',
            'color' => 'Black',
            'stock' => $stock,
            'sku' => 'INT-'.strtoupper(bin2hex(random_bytes(4))),
        ]);
    }

    private function address(User $user, bool $default = true, string $type = 'shipping'): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'type' => $type,
            'recipient_name' => 'Customer',
            'phone' => '+255712345678',
            'region' => 'Dar es Salaam',
            'district' => 'Kinondoni',
            'ward' => 'Mwananyamala',
            'street' => 'Kipanya Street',
            'is_default' => $default,
        ]);
    }

    public function test_checkout_rejects_another_users_address(): void
    {
        $user = $this->user();
        $other = $this->user();
        $variant = $this->product();
        $foreignAddress = $this->address($other);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', 'foreign-address-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $foreignAddress->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['address_id']);

        $this->assertDatabaseCount('wear_orders', 0);
    }

    public function test_first_address_becomes_default_and_deleting_default_promotes_another(): void
    {
        $user = $this->user();
        $first = $this->actingAs($user, 'sanctum')->postJson('/api/v1/addresses', [
            'type' => 'shipping', 'recipient_name' => 'One', 'phone' => '+255700000001',
            'region' => 'Dar', 'district' => 'Kinondoni', 'street' => 'A', 'is_default' => false,
        ])->assertOk()->json('data');

        $second = $this->actingAs($user, 'sanctum')->postJson('/api/v1/addresses', [
            'type' => 'shipping', 'recipient_name' => 'Two', 'phone' => '+255700000002',
            'region' => 'Dar', 'district' => 'Kinondoni', 'street' => 'B', 'is_default' => false,
        ])->assertOk()->json('data');

        $this->assertTrue($first['is_default']);
        $this->assertFalse($second['is_default']);

        $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/addresses/'.$first['id'])->assertNoContent();

        $this->assertDatabaseHas('addresses', ['id' => $second['id'], 'is_default' => true]);
    }

    public function test_checkout_is_idempotent_and_does_not_create_a_second_order_or_reservation(): void
    {
        $user = $this->user();
        $variant = $this->product(3);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $headers = ['Idempotency-Key' => 'duplicate-checkout-001'];

        $first = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();
        $second = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('wear_orders', 1);
        $this->assertDatabaseCount('wear_stock_reservations', 1);
        $this->assertDatabaseCount('payment_transactions', 1);
    }

    public function test_expired_reservation_cancels_order_and_payment_without_changing_stock(): void
    {
        $user = $this->user();
        $variant = $this->product(3);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'expire-order-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();

        $order = WearOrder::findOrFail($response->json('data.id'));
        $reservation = StockReservation::where('wear_order_id', $order->id)->firstOrFail();
        $reservation->update(['expires_at' => now()->subMinute()]);

        Artisan::call('kipanya:expire-stock-reservations');

        $this->assertDatabaseHas('wear_stock_reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::Expired->value,
        ]);
        $this->assertDatabaseHas('wear_orders', [
            'id' => $order->id,
            'status' => OrderStatus::Cancelled->value,
            'payment_status' => PaymentStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'wear_order_id' => $order->id,
            'status' => PaymentStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'stock' => 3]);
    }

    public function test_cancelled_pending_order_marks_payment_cancelled_and_releases_reservation(): void
    {
        $user = $this->user();
        $variant = $this->product(3);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2]);
        $response = $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', 'cancel-order-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])->assertOk();

        $order = WearOrder::findOrFail($response->json('data.id'));
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders/'.$order->order_number.'/cancel')->assertOk();

        $this->assertDatabaseHas('wear_orders', ['id' => $order->id, 'status' => 'cancelled', 'payment_status' => 'cancelled']);
        $this->assertDatabaseHas('payment_transactions', ['wear_order_id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('wear_stock_reservations', ['wear_order_id' => $order->id, 'status' => 'released']);
    }
}
