<?php

namespace Tests\Feature\Commerce;

use App\Actions\Auth\IssueSanctumToken;
use App\Models\Cart\Cart;
use App\Models\User;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutOptionalAddressTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): array
    {
        $user = User::factory()->create([
            'status' => 'active',
            'name' => 'Pickup Customer',
            'email' => 'pickup@example.com',
            'phone' => '+255716000001',
        ]);
        $token = (new IssueSanctumToken)->execute($user);

        $product = WearProduct::create([
            'name' => 'Aero Tee', 'slug' => 'aero-tee', 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 10, 'sku' => 'AERO-M-BLK',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cart->items()->create(['wear_product_variant_id' => $variant->id, 'quantity' => 1]);

        return [$user, $token];
    }

    private function idempotency(): array
    {
        return [
            'Idempotency-Key' => 'kp-'.str_replace('.', 'a', Str::random(32)),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function test_bare_orders_url_redirects_to_the_orders_page(): void
    {
        $this->get('/orders')->assertRedirect('/account/orders');
    }

    public function test_local_format_phones_are_normalized_to_e164(): void
    {
        [$user, $token] = $this->customer();

        $address = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/addresses', [
                'type' => 'shipping',
                'recipient_name' => 'Pickup Customer',
                'phone' => '0716111222',
                'region' => 'Dar es Salaam',
                'district' => 'Ilala',
                'street' => 'Uhuru St 12',
                'is_default' => true,
            ], $this->idempotency())
            ->assertOk()
            ->json();

        // Stored normalized, and the blind index resolves the normalized form.
        $this->assertTrue(
            \App\Models\Commerce\Address::query()
                ->wherePhone('+255716111222')
                ->whereKey($address['data']['id'])
                ->exists()
        );

        // A spaced local-format payment number is accepted and normalized.
        $order = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', [
                'address_id' => $address['data']['id'],
                'payment_method' => 'mobile_money',
                'payment_provider' => 'mpesa',
                'payment_phone' => '0624 643 714',
            ], $this->idempotency())
            ->assertOk()
            ->json();

        $payment = \App\Models\Commerce\PaymentTransaction::query()
            ->where('wear_order_id', \App\Models\Wear\WearOrder::query()->where('order_number', $order['data']['order_number'])->value('id'))
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('+255624643714', $payment->payload['customer_mobile'] ?? null);
    }

    public function test_order_can_be_placed_without_a_delivery_address(): void
    {
        [$user, $token] = $this->customer();

        $order = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', ['notes' => 'Pickup'], $this->idempotency())
            ->assertOk()
            ->json();

        $this->assertNotEmpty($order['data']['order_number']);

        $row = \DB::table('wear_orders')->where('order_number', $order['data']['order_number'])->first();
        $this->assertSame('', $row->delivery_address);
        $this->assertSame('Pickup Customer', $row->customer_name);
        $this->assertSame('+255716000001', $row->customer_phone);
    }

    public function test_order_with_an_address_still_records_it(): void
    {
        [$user, $token] = $this->customer();

        $address = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/addresses', [
                'type' => 'shipping',
                'recipient_name' => 'Pickup Customer',
                'phone' => '+255716000001',
                'region' => 'Dar es Salaam',
                'district' => 'Ilala',
                'ward' => 'Kariakoo',
                'street' => 'Uhuru St 12',
                'is_default' => true,
            ], $this->idempotency())
            ->assertOk()
            ->json();

        $order = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', ['address_id' => $address['data']['id'], 'notes' => 'Leave at gate'], $this->idempotency())
            ->assertOk()
            ->json();

        $row = \DB::table('wear_orders')->where('order_number', $order['data']['order_number'])->first();
        $this->assertStringContainsString('Uhuru St 12', $row->delivery_address);
        $this->assertSame('Leave at gate', $row->notes);
    }
}
