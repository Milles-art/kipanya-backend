<?php

namespace Tests\Feature\Commerce;

use App\Actions\Auth\IssueSanctumToken;
use App\Enums\Commerce\PaymentStatus;
use App\Models\Cart\Cart;
use App\Models\Commerce\PaymentTransaction;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Services\Payments\PaymentStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutLastUsedPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $suffix): array
    {
        $user = User::factory()->create([
            'status' => 'active',
            'name' => 'OneTap Customer',
            'email' => "onetap-{$suffix}@example.com",
            'phone' => '+255716'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
        ]);
        $token = (new IssueSanctumToken)->execute($user);

        $product = WearProduct::create([
            'name' => 'OneTap Tee', 'slug' => "onetap-tee-{$suffix}", 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 10, 'sku' => "ONETAP-M-{$suffix}",
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

    private function placeOrder(User $user): string
    {
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/checkout', [
                'payment_method' => 'mobile_money',
                'payment_provider' => 'mpesa',
                'payment_phone' => '0624643714',
            ], $this->idempotency())
            ->assertOk();

        return $response->json('data.order_number');
    }

    public function test_returns_null_when_never_paid(): void
    {
        [$user] = $this->customer(Str::lower(Str::random(6)));
        $this->placeOrder($user);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/checkout/last-used-payment')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_returns_provider_and_phone_after_a_paid_order(): void
    {
        [$user] = $this->customer(Str::lower(Str::random(6)));
        $orderNumber = $this->placeOrder($user);

        $order = WearOrder::query()->where('order_number', $orderNumber)->firstOrFail();
        $payment = PaymentTransaction::query()->where('wear_order_id', $order->id)->latest('id')->firstOrFail();

        app(PaymentStateMachine::class)->transition($payment, PaymentStatus::Paid, [
            'transid' => 'TEST-ONETAP-1',
            'amount' => (float) $order->total,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/checkout/last-used-payment')
            ->assertOk()
            ->assertJsonPath('data.provider', 'mpesa')
            ->assertJsonPath('data.phone', '+255624643714');
    }

    public function test_never_exposes_another_customers_number(): void
    {
        [$user] = $this->customer(Str::lower(Str::random(6)));
        [$other] = $this->customer(Str::lower(Str::random(6)));
        $orderNumber = $this->placeOrder($user);

        $order = WearOrder::query()->where('order_number', $orderNumber)->firstOrFail();
        $payment = PaymentTransaction::query()->where('wear_order_id', $order->id)->latest('id')->firstOrFail();

        app(PaymentStateMachine::class)->transition($payment, PaymentStatus::Paid, [
            'transid' => 'TEST-ONETAP-2',
            'amount' => (float) $order->total,
        ]);

        $this->actingAs($other, 'sanctum')
            ->getJson('/api/v1/checkout/last-used-payment')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_guests_cannot_read_it(): void
    {
        $this->getJson('/api/v1/checkout/last-used-payment')->assertUnauthorized();
    }
}
