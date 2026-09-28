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

    // ---- card payments: no cardholder data on this domain

    public function test_card_is_not_an_accepted_payment_method(): void
    {
        [$user] = $this->customer();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', ['payment_method' => 'card'], $this->idempotency())
            ->assertStatus(422)
            ->assertJsonValidationErrors('payment_method');

        $this->assertSame(0, \App\Models\Wear\WearOrder::query()->count());
    }

    public function test_card_fields_sent_by_a_client_are_never_stored_or_echoed(): void
    {
        [$user] = $this->customer();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', [
                'payment_method' => 'mobile_money',
                'payment_provider' => 'mpesa',
                'payment_phone' => '0624643714',
                'card_number' => '4111111111111111',
                'card_cvv' => '123',
                'card_expiry' => '12/30',
                'card_name' => 'Test Person',
            ], $this->idempotency())
            ->assertOk();

        $this->assertStringNotContainsString('4111111111111111', $response->getContent());

        foreach (\Illuminate\Support\Facades\DB::getSchemaBuilder()->getTableListing() as $table) {
            foreach (\Illuminate\Support\Facades\DB::table($table)->get() as $row) {
                $this->assertStringNotContainsString('4111111111111111', json_encode($row), "Card number found in {$table}");
            }
        }
    }

    public function test_the_checkout_page_does_not_render_card_inputs(): void
    {
        [$user] = $this->customer();

        $html = $this->actingAs($user, 'sanctum')->get('/checkout')->assertOk()->getContent();

        foreach (['card_number', 'card_cvv', 'card_expiry', 'cc-number', 'cc-csc'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, "Checkout must not collect {$needle}");
        }
    }

    // ---- a clear, direct payment journey

    public function test_the_checkout_page_sends_the_customer_straight_to_the_payment_page(): void
    {
        [$user] = $this->customer();

        $html = $this->actingAs($user, 'sanctum')->get('/checkout')->assertOk()->getContent();

        $this->assertStringContainsString('payment_gateway_url', $html, 'After placing the order the page must follow the payment URL.');
        $this->assertStringContainsString('Next step:', $html);
    }

    public function test_the_order_status_page_starts_with_payment_as_the_active_step(): void
    {
        [$user] = $this->customer();

        $html = $this->actingAs($user, 'sanctum')->get('/orders/KP-TEST-0001')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/os-step-wrap active" data-os-step="2"/', $html, 'Payment must not be shown as already done.');
        $this->assertDoesNotMatchRegularExpression('/os-step-wrap (done|success)" data-os-step="2"/', $html);
        $this->assertStringContainsString('Tap <strong>Pay now</strong>', $html);
    }

    public function test_a_placed_order_exposes_the_payment_url_the_page_redirects_to(): void
    {
        [$user] = $this->customer();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', ['payment_method' => 'mobile_money', 'payment_provider' => 'mpesa', 'payment_phone' => '0624643714'], $this->idempotency())
            ->assertOk();

        $urls = collect($response->json('data.payment'))->pluck('payment_gateway_url')->filter();
        $this->assertNotEmpty($urls, 'The order response must carry the payment page URL.');
    }
}
