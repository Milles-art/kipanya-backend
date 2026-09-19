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

class CheckoutWebFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_renders_user_and_preview(): void
    {
        $user = User::factory()->create(['status' => 'active', 'name' => 'Grace M', 'email' => 'grace@example.com', 'phone' => '+255700000001']);
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
        $cart->items()->create(['wear_product_variant_id' => $variant->id, 'quantity' => 2]);

        $html = $this->withUnencryptedCookies(['kp_web_session' => $token])
            ->get('/checkout')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="kp-signed-in" content="1"', $html);
        $this->assertStringContainsString('window.KP_USER = ', $html);
        $this->assertStringContainsString('Grace M', $html);
        $this->assertStringContainsString('grace@example.com', $html);
        $this->assertStringContainsString('data-checkout-page', $html);
        $this->assertStringContainsString('data-payment-card', $html);
        $this->assertStringContainsString('data-place-order', $html);
        $this->assertMatchesRegularExpression('/<script nonce="[^"]+">window\.KP_USER/', $html, 'KP_USER script must be nonced for CSP');
        $this->assertMatchesRegularExpression('/<script nonce="[^"]+">[\s\S]*?bindPaymentMethods/', $html, 'checkout inline script must be nonced for CSP');

        $json = $this->withUnencryptedCookies(['kp_web_session' => $token])
            ->getJson('/api/v1/cart/checkout/preview')
            ->assertOk()
            ->json();

        $this->assertSame('TZS', $json['data']['currency']);
        $this->assertCount(1, $json['data']['items']);
        $this->assertSame('Aero Tee', $json['data']['items'][0]['name']);
        $this->assertSame('Black', $json['data']['items'][0]['color']);
        $this->assertSame(50000.0, (float) $json['data']['total']);

        $checkmarks = [
            "fetch('/api/v1/cart/checkout/preview'",
            "/api/v1/checkout'",
            'bindPaymentMethods',
            'loadDefaultAddress',
            'data-use-current-location',
            '/api/v1/addresses',
            'window.KP_USER',
        ];
        foreach ($checkmarks as $mark) {
            $this->assertStringContainsString($mark, $html, "Missing marker: {$mark}");
        }

        // Full chain the page performs: save address -> place order.
        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];

        $address = $this->withUnencryptedCookies(['kp_web_session' => $token])
            ->postJson('/api/v1/addresses', [
                'type' => 'shipping',
                'recipient_name' => 'Grace M',
                'phone' => '+255700000001',
                'region' => 'Dar es Salaam',
                'district' => 'Ilala',
                'ward' => 'Kariakoo',
                'street' => 'Uhru St 12',
                'is_default' => true,
            ], $headers)
            ->assertOk()
            ->json();

        $order = $this->withUnencryptedCookies(['kp_web_session' => $token])
            ->postJson('/api/v1/checkout', [
                'address_id' => $address['data']['id'],
                'notes' => 'Leave at gate',
            ], ['Idempotency-Key' => 'kp-'.str_replace('.', 'a', Str::random(32)), 'Accept' => 'application/json', 'Content-Type' => 'application/json'])
            ->assertOk()
            ->json();

        $this->assertNotEmpty($order['data']['order_number']);
        $this->assertNotEmpty($order['data']['payment']);
        $this->assertArrayHasKey('payment_gateway_url', $order['data']['payment'][0]);
    }
}