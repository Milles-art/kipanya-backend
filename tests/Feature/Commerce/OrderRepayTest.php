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

class OrderRepayTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): array
    {
        $suffix = Str::lower(Str::random(8));
        $user = User::factory()->create([
            'status' => 'active',
            'name' => 'Repay Customer',
            'email' => "repay-{$suffix}@example.com",
            'phone' => '+255716'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
        ]);
        $token = (new IssueSanctumToken)->execute($user);

        $product = WearProduct::create([
            'name' => 'Repay Tee', 'slug' => "repay-tee-{$suffix}", 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 10, 'sku' => "REPAY-M-{$suffix}",
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

    private function latestPayment(string $orderNumber): PaymentTransaction
    {
        $orderId = WearOrder::query()->where('order_number', $orderNumber)->value('id');

        return PaymentTransaction::query()->where('wear_order_id', $orderId)->latest('id')->firstOrFail();
    }

    public function test_repay_mints_a_fresh_payment_url_for_a_pending_order(): void
    {
        [$user] = $this->customer();
        $orderNumber = $this->placeOrder($user);
        $first = $this->latestPayment($orderNumber);

        // Age the token past the reuse window so repay mints a new one.
        $first->update(['initiated_at' => now()->subMinutes(5)]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$orderNumber}/repay")
            ->assertOk();

        $urls = collect($response->json('data.payment'))->pluck('payment_gateway_url')->filter();
        $this->assertNotEmpty($urls, 'Repay must return a fresh payment page URL.');

        $fresh = $this->latestPayment($orderNumber);
        $this->assertNotSame($first->provider_reference, $fresh->provider_reference);
        $this->assertSame(PaymentStatus::Cancelled, $first->fresh()->status);
        $this->assertSame(PaymentStatus::Pending, $fresh->status);
    }

    public function test_repay_reuses_a_token_minted_moments_ago(): void
    {
        [$user] = $this->customer();
        $orderNumber = $this->placeOrder($user);
        $first = $this->latestPayment($orderNumber);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$orderNumber}/repay")
            ->assertOk();

        // No new row: the fresh token is reused, so double taps are safe.
        $this->assertSame($first->provider_reference, $this->latestPayment($orderNumber)->provider_reference);
        $this->assertSame(
            1,
            PaymentTransaction::query()
                ->where('wear_order_id', WearOrder::query()->where('order_number', $orderNumber)->value('id'))
                ->count()
        );
    }

    public function test_repay_is_forbidden_for_other_customers_orders(): void
    {
        [$owner] = $this->customer();
        [$other] = $this->customer();
        $orderNumber = $this->placeOrder($owner);

        $this->actingAs($other, 'sanctum')
            ->postJson("/api/v1/orders/{$orderNumber}/repay")
            ->assertForbidden();
    }

    public function test_repay_rejects_an_already_paid_order(): void
    {
        [$user] = $this->customer();
        $orderNumber = $this->placeOrder($user);
        $payment = $this->latestPayment($orderNumber);
        $order = WearOrder::query()->where('order_number', $orderNumber)->firstOrFail();

        app(PaymentStateMachine::class)->transition($payment, PaymentStatus::Paid, [
            'transid' => 'TEST-REPay-1',
            'amount' => (float) $order->total,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/orders/{$orderNumber}/repay")
            ->assertStatus(422);
    }

    public function test_guests_cannot_repay(): void
    {
        $this->postJson('/api/v1/orders/KP-NOPE/repay')->assertUnauthorized();
    }
}
