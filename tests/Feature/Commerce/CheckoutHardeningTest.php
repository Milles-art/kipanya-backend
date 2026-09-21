<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Models\Commerce\Address;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * N-02 (stable lock order), N-03 (no order-id oracle), N-04 (internal notes not exposed),
 * N-05 (idempotency key cannot replay a different request).
 */
final class CheckoutHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['status' => UserStatus::Active, 'phone_verified_at' => now()]);
    }

    private function variant(int $stock = 10): WearProductVariant
    {
        $product = WearProduct::create([
            'name' => 'Hardening Tee', 'slug' => 'hardening-tee-'.uniqid(), 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);

        return WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => $stock, 'sku' => 'HRD-'.strtoupper(bin2hex(random_bytes(4))),
        ]);
    }

    private function address(User $user, string $street = 'Hardening Street'): Address
    {
        return Address::create([
            'user_id' => $user->id, 'type' => 'shipping', 'recipient_name' => 'Customer',
            'phone' => '+255712345678', 'region' => 'Dar es Salaam', 'district' => 'Kinondoni',
            'ward' => 'Mwananyamala', 'street' => $street, 'is_default' => true,
        ]);
    }

    private function addToCart(User $user, WearProductVariant $variant, int $qty = 1): void
    {
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id, 'quantity' => $qty,
        ])->assertSuccessful();
    }

    private function checkout(User $user, Address $address, string $key, ?string $notes = null): TestResponse
    {
        return $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/checkout', array_filter(['address_id' => $address->id, 'notes' => $notes]));
    }

    // ----------------------------------------------------------------- N-05

    public function test_replaying_the_same_request_returns_the_same_order(): void
    {
        $user = $this->user();
        $address = $this->address($user);
        $this->addToCart($user, $this->variant());

        $first = $this->checkout($user, $address, 'idem-same-request-0001', 'leave at gate');
        $second = $this->checkout($user, $address, 'idem-same-request-0001', 'leave at gate');

        $first->assertSuccessful();
        $second->assertSuccessful();
        $this->assertSame($first->json('data.order_number'), $second->json('data.order_number'));
        $this->assertSame(1, WearOrder::query()->count());
    }

    public function test_reusing_a_key_with_a_different_address_is_rejected(): void
    {
        $user = $this->user();
        $address = $this->address($user);
        $other = $this->address($user, 'A Different Street');
        $this->addToCart($user, $this->variant());

        $this->checkout($user, $address, 'idem-diff-address-0001')->assertSuccessful();

        $this->checkout($user, $other, 'idem-diff-address-0001')
            ->assertStatus(422)
            ->assertJsonValidationErrors('idempotency_key');
    }

    public function test_reusing_a_key_with_different_notes_is_rejected(): void
    {
        $user = $this->user();
        $address = $this->address($user);
        $this->addToCart($user, $this->variant());

        $this->checkout($user, $address, 'idem-diff-notes-00001', 'first note')->assertSuccessful();

        $this->checkout($user, $address, 'idem-diff-notes-00001', 'changed note')
            ->assertStatus(422)
            ->assertJsonValidationErrors('idempotency_key');
    }

    public function test_another_users_key_is_still_refused(): void
    {
        $owner = $this->user();
        $this->addToCart($owner, $this->variant());
        $this->checkout($owner, $this->address($owner), 'idem-owned-key-000001')->assertSuccessful();

        $other = $this->user();
        $this->addToCart($other, $this->variant());

        $this->checkout($other, $this->address($other), 'idem-owned-key-000001')->assertStatus(422);
    }

    // ----------------------------------------------------------------- N-04

    public function test_internal_fulfillment_notes_are_not_returned_to_the_customer(): void
    {
        $user = $this->user();
        $this->addToCart($user, $this->variant());
        $order = WearOrder::findOrFail($this->checkout($user, $this->address($user), 'idem-notes-leak-0001')->json('data.id'));
        $order->update(['fulfillment_notes' => 'INTERNAL: customer is a repeat refunder, gate code 4471']);

        $body = $this->actingAs($user, 'sanctum')->getJson('/api/v1/orders/'.$order->order_number)
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('INTERNAL', $body);
        $this->assertStringNotContainsString('4471', $body);
        $this->assertStringNotContainsString('fulfillment_notes', $body);
    }

    // ----------------------------------------------------------------- N-03

    public function test_return_request_does_not_reveal_which_order_ids_exist(): void
    {
        $owner = $this->user();
        $this->addToCart($owner, $this->variant());
        $orderId = $this->checkout($owner, $this->address($owner), 'idem-return-oracle-01')->json('data.id');

        $attacker = $this->user();
        $payload = ['request_type' => 'return', 'reason' => 'damaged', 'item_ids' => [1]];

        $foreign = $this->actingAs($attacker, 'sanctum')->postJson('/api/v1/returns', $payload + ['order_id' => $orderId]);
        $missing = $this->actingAs($attacker, 'sanctum')->postJson('/api/v1/returns', $payload + ['order_id' => 999999]);

        $this->assertSame($missing->status(), $foreign->status(), 'Foreign and non-existent orders must be indistinguishable.');
        $this->assertSame(404, $foreign->status());
    }

    // ----------------------------------------------------------------- N-02

    public function test_variants_are_locked_in_ascending_id_order_regardless_of_cart_order(): void
    {
        $user = $this->user();
        $low = $this->variant();
        $high = $this->variant();
        $address = $this->address($user);

        // Add the HIGHER id first so cart order is the reverse of id order.
        $this->addToCart($user, $high);
        $this->addToCart($user, $low);

        DB::enableQueryLog();
        $this->checkout($user, $address, 'idem-lock-order-00001')->assertSuccessful();

        $locked = collect(DB::getQueryLog())
            ->filter(fn (array $q) => str_contains($q['query'], 'from "wear_product_variants" where "wear_product_variants"."id" = ?'))
            ->map(fn (array $q) => (int) $q['bindings'][0])
            ->unique()
            ->values()
            ->all();

        $this->assertSame([$low->id, $high->id], array_slice($locked, -2), 'Variant rows must be locked in ascending id order.');
    }
}
