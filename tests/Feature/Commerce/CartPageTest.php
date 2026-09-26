<?php

namespace Tests\Feature\Commerce;

use App\Actions\Auth\IssueSanctumToken;
use App\Models\Cart\Cart;
use App\Models\User;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_page_renders_empty_state_for_guests(): void
    {
        $html = $this->get('/cart')->assertOk()->getContent();

        $this->assertStringContainsString('Your bag', $html);
        $this->assertStringContainsString('data-cart-page', $html);
        $this->assertStringContainsString('Your bag is empty', $html);
    }

    public function test_cart_page_renders_items_and_subtotal_for_signed_in_user(): void
    {
        $user = User::factory()->create(['status' => 'active']);
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
            ->get('/cart')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Aero Tee', $html);
        $this->assertStringContainsString('data-cart-version="2"', $html);
        $this->assertStringContainsString('data-variant-id="'.$variant->id.'"', $html);
        $this->assertStringContainsString('data-unit-price="25000"', $html);
        $this->assertStringContainsString('TZS 50,000', $html);
        $this->assertStringContainsString('data-cart-content', $html);
    }
}
