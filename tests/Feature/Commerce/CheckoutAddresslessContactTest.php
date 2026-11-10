<?php

namespace Tests\Feature\Commerce;

use App\Models\Cart\Cart;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAddresslessContactTest extends TestCase
{
    use RefreshDatabase;

    private function customerWithoutPhone(): User
    {
        $user = User::factory()->create(['status' => 'active', 'name' => 'Social Customer', 'email' => 'social-checkout@example.test', 'phone' => null]);
        $product = WearProduct::create(['name' => 'Test Tee', 'slug' => 'test-tee', 'price' => 25000, 'category' => 'shirts', 'is_active' => true]);
        $variant = WearProductVariant::create(['wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black', 'stock' => 5, 'sku' => 'CONTACT-M-BLK']);
        Cart::firstOrCreate(['user_id' => $user->id])->items()->create(['wear_product_variant_id' => $variant->id, 'quantity' => 1]);
        return $user;
    }

    public function test_addressless_social_customer_uses_payment_phone_without_modifying_account_phone(): void
    {
        $user = $this->customerWithoutPhone();
        $headers = ['Idempotency-Key' => 'addressless-social-checkout-0001'];
        $payload = ['payment_method' => 'mobile_money', 'payment_provider' => 'mpesa', 'payment_phone' => '0716111222'];
        $first = $this->actingAs($user, 'sanctum')->postJson('/api/v1/checkout/place', $payload, $headers)->assertOk();
        $first->assertJsonPath('data.customer.phone', '+255716111222');
        $this->assertSame('', WearOrder::firstOrFail()->delivery_address);
        $this->assertNull($user->fresh()->phone);
        $retry = $this->actingAs($user, 'sanctum')->postJson('/api/v1/checkout/place', $payload, $headers)->assertOk();
        $this->assertSame($first->json('data.order_number'), $retry->json('data.order_number'));
        $this->assertSame(1, WearOrder::count());
    }

    public function test_missing_contact_and_payment_phone_is_a_validation_error_not_a_database_failure(): void
    {
        $user = $this->customerWithoutPhone();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/checkout/place', [], ['Idempotency-Key' => 'addressless-no-contact-0001'])->assertUnprocessable()->assertJsonValidationErrors('payment_phone');
        $this->assertSame(0, WearOrder::count());
    }
}
