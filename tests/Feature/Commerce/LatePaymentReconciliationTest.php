<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Models\Commerce\Address;
use App\Models\Commerce\PaymentTransaction;
use App\Models\User;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Regression tests for F-02: money captured by the provider after the order was
 * cancelled, the reservation expired, or stock disappeared used to be recorded
 * as a plain "failed" payment (or a 422) with no refund flag.
 */
class LatePaymentReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private const API_KEY = 'selcom_test_key';

    private const API_SECRET = 'selcom_test_secret';

    private function user(): User
    {
        return User::factory()->create([
            'status' => UserStatus::Active,
            'phone_verified_at' => now(),
        ]);
    }

    private function product(int $stock = 5): array
    {
        $product = WearProduct::create([
            'name' => 'Webhook Tee', 'slug' => 'webhook-tee-'.uniqid(), 'price' => 25000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => $stock, 'sku' => 'WHK-'.strtoupper(bin2hex(random_bytes(4))),
        ]);

        return [$product, $variant];
    }

    private function address(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id,
            'type' => 'shipping',
            'recipient_name' => 'Webhook Customer',
            'phone' => '+255712345678',
            'region' => 'Dar es Salaam',
            'district' => 'Kinondoni',
            'ward' => 'Mwananyamala',
            'street' => 'Webhook Street',
            'is_default' => true,
        ]);
    }

    private function checkout(string $idempotency): TestResponse
    {
        $user = $this->user();
        [, $variant] = $this->product(5);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        return $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', $idempotency)
            ->postJson('/api/v1/checkout', ['address_id' => $address->id]);
    }

    private function paymentFor(TestResponse $response): PaymentTransaction
    {
        return PaymentTransaction::findOrFail($response->json('data.payment.0.id'));
    }

    private function sign(string $timestamp, array $fields, array $body): string
    {
        $parts = ['timestamp='.$timestamp];

        foreach ($fields as $field) {
            $parts[] = $field.'='.(string) $body[$field];
        }

        return base64_encode(hash_hmac('sha256', implode('&', $parts), self::API_SECRET, true));
    }

    private function postWebhook(array $body, array $headers = []): TestResponse
    {
        config()->set('services.selcom.api_key', self::API_KEY);
        config()->set('services.selcom.api_secret', self::API_SECRET);

        $timestamp = now()->format('Y-m-d\TH:i:sP');
        $fields = ['transid', 'order_id', 'reference', 'result', 'resultcode', 'payment_status'];
        $digest = $this->sign($timestamp, $fields, $body);

        return $this->postJson('/api/webhooks/selcom', $body, array_merge([
            'Authorization' => 'SELCOM '.base64_encode(self::API_KEY),
            'Timestamp' => $timestamp,
            'Digest-Method' => 'HS256',
            'Digest' => $digest,
            'Signed-Fields' => implode(',', $fields),
        ], $headers));
    }

    private function webhookBody(PaymentTransaction $payment, array $overrides = []): array
    {
        return array_merge([
            'result' => 'SUCCESS',
            'resultcode' => '000',
            'order_id' => $payment->order->order_number,
            'reference' => $payment->provider_reference,
            'transid' => 'TXN-'.Str::upper(Str::random(10)),
            'channel' => 'TIGOPESATZ',
            'amount' => (string) ((int) round((float) $payment->order->total)),
            'phone' => '255712345678',
            'payment_status' => 'COMPLETED',
        ], $overrides);
    }

    private function assertNeedsRefund(PaymentTransaction $payment, int $expectedStock): void
    {
        $payment->refresh();

        $this->assertSame('reconciliation_required', $payment->status->value);
        $this->assertTrue($payment->payload['needs_refund'] ?? false, 'A captured payment must be flagged for refund.');
        $this->assertNotEmpty($payment->payload['reconciliation_reason'] ?? null);
        $this->assertNotEmpty($payment->provider_transid, 'The provider transaction id must be recorded.');
        $this->assertDatabaseHas('wear_orders', [
            'id' => $payment->wear_order_id,
            'status' => 'cancelled',
            'payment_status' => 'reconciliation_required',
        ]);

        $variantId = $payment->order->items->first()->wear_product_variant_id;
        $this->assertSame($expectedStock, (int) WearProductVariant::query()->whereKey($variantId)->value('stock'));
        $this->assertDatabaseMissing('wear_inventory_movements', [
            'wear_product_variant_id' => $variantId,
            'reason' => 'Sale',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.needs_refund']);
    }

    public function test_payment_confirmed_after_customer_cancelled_is_flagged_for_refund(): void
    {
        $payment = $this->paymentFor($this->checkout('late-after-cancel-0001'));
        $user = User::findOrFail($payment->user_id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders/'.$payment->order->order_number.'/cancel')
            ->assertOk();

        $this->postWebhook($this->webhookBody($payment))
            ->assertOk()
            ->assertJsonPath('verified', true)
            ->assertJsonPath('result', 'OK');

        $this->assertNeedsRefund($payment, 5);
    }

    public function test_payment_confirmed_after_reservation_expired_is_flagged_for_refund(): void
    {
        $payment = $this->paymentFor($this->checkout('late-after-expiry-0001'));

        DB::table('wear_stock_reservations')
            ->where('wear_order_id', $payment->wear_order_id)
            ->update(['expires_at' => now()->subMinutes(5)]);

        $this->postWebhook($this->webhookBody($payment))->assertOk();

        $this->assertNeedsRefund($payment, 5);
    }

    public function test_payment_confirmed_when_stock_is_gone_is_flagged_for_refund(): void
    {
        $payment = $this->paymentFor($this->checkout('late-no-stock-0001'));
        $variantId = $payment->order->items->first()->wear_product_variant_id;

        WearProductVariant::query()->whereKey($variantId)->update(['stock' => 1]);

        $this->postWebhook($this->webhookBody($payment))->assertOk();

        $this->assertNeedsRefund($payment, 1);
    }

    public function test_a_repeated_late_callback_is_idempotent(): void
    {
        $payment = $this->paymentFor($this->checkout('late-repeat-0001'));
        $user = User::findOrFail($payment->user_id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders/'.$payment->order->order_number.'/cancel')
            ->assertOk();

        $body = $this->webhookBody($payment);
        $this->postWebhook($body)->assertOk();
        $this->postWebhook($body)->assertOk()->assertJsonPath('duplicate', true);

        $this->assertNeedsRefund($payment, 5);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'payment.needs_refund')->count());
    }

    public function test_customer_cannot_cancel_while_a_payment_is_in_progress(): void
    {
        $payment = $this->paymentFor($this->checkout('cancel-in-progress-0001'));
        $user = User::findOrFail($payment->user_id);

        $payment->update(['status' => PaymentStatus::InProgress]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/orders/'.$payment->order->order_number.'/cancel')
            ->assertStatus(422);

        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);
    }

    public function test_a_normal_payment_still_completes(): void
    {
        $payment = $this->paymentFor($this->checkout('late-control-0001'));

        $this->postWebhook($this->webhookBody($payment))->assertOk();

        $payment->refresh();
        $this->assertSame('paid', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'confirmed']);
    }
}
