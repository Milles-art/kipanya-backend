<?php

namespace Tests\Feature\Commerce;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Models\Cart\Cart;
use App\Models\Commerce\PaymentTransaction;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaySimulatorTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): User
    {
        return User::factory()->create(['status' => 'active']);
    }

    private function stockedVariant(): WearProductVariant
    {
        $product = WearProduct::create([
            'name' => 'Aero Tee', 'slug' => 'aero-tee-'.Str::random(6), 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);

        return WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 10, 'sku' => 'AERO-M-'.Str::upper(Str::random(4)),
        ]);
    }

    private function placeOrder(User $user, ?int $addressId = null): WearOrder
    {
        $variant = $this->stockedVariant();

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cart->items()->create(['wear_product_variant_id' => $variant->id, 'quantity' => 1]);

        $payload = $addressId ? ['address_id' => $addressId] : [];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', $payload, [
                'Idempotency-Key' => 'kp-'.str_replace('.', 'a', Str::random(32)),
                'Accept' => 'application/json',
            ])
            ->assertOk();

        return WearOrder::query()->where('order_number', $response->json('data.order_number'))->firstOrFail();
    }

    public function test_simulator_page_renders_for_the_owning_customer(): void
    {
        $user = $this->customer();
        $order = $this->placeOrder($user);

        $this->actingAs($user, 'sanctum')
            ->get("/pay/simulate/{$order->order_number}")
            ->assertOk()
            ->assertSee($order->order_number, false)
            ->assertSee('Simulate successful payment', false);
    }

    public function test_simulator_hides_other_customers_orders(): void
    {
        $user = $this->customer();
        $other = $this->customer();
        $order = $this->placeOrder($other);

        $this->actingAs($user, 'sanctum')
            ->get("/pay/simulate/{$order->order_number}")
            ->assertNotFound();
    }

    public function test_simulated_success_completes_payment_and_confirms_order(): void
    {
        $user = $this->customer();
        $order = $this->placeOrder($user);

        $this->actingAs($user, 'sanctum')
            ->post("/pay/simulate/{$order->order_number}/complete")
            ->assertRedirect("/orders/{$order->order_number}");

        $payment = PaymentTransaction::query()->where('wear_order_id', $order->id)->latest('id')->firstOrFail();

        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
    }

    public function test_simulated_failure_marks_payment_failed(): void
    {
        $user = $this->customer();
        $order = $this->placeOrder($user);

        $this->actingAs($user, 'sanctum')
            ->post("/pay/simulate/{$order->order_number}/fail")
            ->assertRedirect("/orders/{$order->order_number}");

        $payment = PaymentTransaction::query()->where('wear_order_id', $order->id)->latest('id')->firstOrFail();

        $this->assertSame(PaymentStatus::Failed, $payment->status);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/pay/simulate/KP-0000')->assertRedirect('/login');
    }
}
