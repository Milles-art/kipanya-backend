<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Models\Commerce\Address;
use App\Models\Commerce\PaymentTransaction;
use App\Models\User;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SelcomWebhookTest extends TestCase
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

    public function test_valid_completed_webhook_marks_payment_paid_and_order_confirmed(): void
    {
        $response = $this->checkout('webhook-success-0001');
        $payment = $this->paymentFor($response);

        $callback = $this->postWebhook($this->webhookBody($payment));

        $callback->assertOk()->assertJsonPath('verified', true);

        $payment->refresh();
        $this->assertSame('paid', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', [
            'id' => $payment->wear_order_id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
        $this->assertDatabaseHas('wear_stock_reservations', [
            'wear_order_id' => $payment->wear_order_id,
            'status' => 'fulfilled',
        ]);
        $this->assertDatabaseHas('wear_inventory_movements', [
            'wear_product_variant_id' => $payment->order->items->first()->wear_product_variant_id,
            'quantity' => -2,
            'reason' => 'Sale',
        ]);

        $this->assertSame('TIGOPESATZ', $payment->payload['provider_callback']['channel']);
    }

    public function test_invalid_webhook_signature_is_rejected_without_state_changes(): void
    {
        $response = $this->checkout('webhook-badsig-0001');
        $payment = $this->paymentFor($response);

        $callback = $this->postWebhook($this->webhookBody($payment), ['Digest' => 'not-the-real-digest']);

        $callback->assertStatus(401)->assertJsonPath('error', 'invalid_signature');
        $this->assertStringNotContainsString(self::API_SECRET, $callback->getContent());
        $this->assertStringNotContainsString(self::API_KEY, $callback->getContent());

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);
    }

    public function test_missing_digest_method_is_rejected(): void
    {
        $response = $this->checkout('webhook-nodigest-0001');
        $payment = $this->paymentFor($response);

        $callback = $this->postWebhook($this->webhookBody($payment), ['Digest-Method' => 'RS256']);

        $callback->assertStatus(401);
        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
    }

    public function test_webhook_for_unknown_order_is_rejected(): void
    {
        $response = $this->checkout('webhook-unknown-0001');
        $payment = $this->paymentFor($response);

        $callback = $this->postWebhook($this->webhookBody($payment, [
            'order_id' => 'KP-NO-SUCH-ORDER',
        ]));

        $callback->assertStatus(404)->assertJsonPath('error', 'order_not_found');

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);
    }

    public function test_webhook_with_mismatched_reference_cannot_settle_another_order(): void
    {
        $first = $this->paymentFor($this->checkout('webhook-refone-0001'));
        $second = $this->paymentFor($this->checkout('webhook-reftwo-0001'));

        $callback = $this->postWebhook($this->webhookBody($first, [
            'reference' => $second->provider_reference,
        ]));

        $callback->assertStatus(404)->assertJsonPath('error', 'reference_mismatch');

        $first->refresh();
        $this->assertSame('pending', $first->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $first->wear_order_id, 'status' => 'pending_payment']);
        $this->assertDatabaseHas('wear_orders', ['id' => $second->wear_order_id, 'status' => 'pending_payment']);
    }

    public function test_webhook_amount_mismatch_is_not_honoured(): void
    {
        $response = $this->checkout('webhook-amt-0001');
        $payment = $this->paymentFor($response);

        $callback = $this->postWebhook($this->webhookBody($payment, [
            'amount' => (string) (((int) round((float) $payment->order->total)) + 1),
        ]));

        $callback->assertStatus(422)->assertJsonPath('error', 'invalid_callback');

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);
        $this->assertDatabaseHas('wear_stock_reservations', ['wear_order_id' => $payment->wear_order_id, 'status' => 'active']);
    }

    public function test_duplicate_completed_webhooks_are_idempotent(): void
    {
        $user = $this->user();
        [, $variant] = $this->product(5);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', 'webhook-dup-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id]);

        $payment = $this->paymentFor($response);
        $body = $this->webhookBody($payment);

        $this->postWebhook($body)->assertOk();
        $this->postWebhook($body)->assertOk();

        $this->assertSame('paid', $payment->fresh()->status->value);
        $this->assertDatabaseCount('wear_inventory_movements', 2); // 1 reservation (qty 0) + 1 sale
        $this->assertDatabaseHas('wear_inventory_movements', [
            'wear_product_variant_id' => $variant->id,
            'reason' => 'Sale',
            'quantity' => -2,
        ]);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'confirmed']);
    }

    public function test_non_completed_webhook_does_not_mark_order_paid(): void
    {
        $response = $this->checkout('webhook-pending-0001');
        $payment = $this->paymentFor($response);

        $callback = $this->postWebhook($this->webhookBody($payment, [
            'payment_status' => 'PENDING',
        ]));

        $callback->assertOk()->assertJsonPath('verified', true);

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);
        $this->assertArrayHasKey('provider_state', $payment->payload);
    }

    public function test_get_request_cannot_finalise_a_payment(): void
    {
        $response = $this->checkout('webhook-get-0001');
        $payment = $this->paymentFor($response);

        $this->get('/api/webhooks/selcom')->assertStatus(405);

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);
    }

    public function test_checkout_initiation_exposes_gateway_url_but_does_not_mark_paid(): void
    {
        config()->set('services.selcom', [
            'base_url' => 'https://selcom.example.test',
            'api_key' => self::API_KEY,
            'api_secret' => self::API_SECRET,
            'vendor_id' => 'KIPANYA',
            'currency' => 'TZS',
            'redirect_url' => 'https://kipanya.example/payment/success',
            'cancel_url' => 'https://kipanya.example/payment/cancel',
            'webhook_url' => 'https://kipanya.example/api/webhooks/selcom',
            'timeout' => 10,
        ]);

        $encoded = base64_encode('https://pay.example/launch/pg');
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'reference' => '0289999288',
                'resultcode' => '000',
                'result' => 'SUCCESS',
                'data' => [[
                    'gateway_buyer_uuid' => 'buyer-uuid',
                    'payment_gateway_url' => $encoded,
                ]],
            ], 200),
        ]);

        $user = $this->user();
        [, $variant] = $this->product(5);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', 'webhook-selcom-init-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'pending_payment')
            ->assertJsonPath('data.payment.0.status', 'pending')
            ->assertJsonPath('data.payment.0.payment_gateway_url', 'https://pay.example/launch/pg');

        $this->assertDatabaseHas('payment_transactions', ['status' => 'pending']);
        $this->assertDatabaseHas('wear_orders', ['status' => 'pending_payment']);
    }

    public function test_refresh_status_with_pending_gateway_response_leaves_payment_pending(): void
    {
        $response = $this->checkout('webhook-refresh-0001');
        $payment = $this->paymentFor($response);

        app(PaymentService::class)->refreshStatus($payment);

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);
    }

    public function test_webhook_timestamp_too_old_is_rejected(): void
    {
        $response = $this->checkout('webhook-oldts-0001');
        $payment = $this->paymentFor($response);

        // Create a body with an old timestamp (20 minutes ago - well beyond 10 min limit)
        $oldTimestamp = now()->subMinutes(20)->format('Y-m-d\TH:i:sP');
        $fields = ['transid', 'order_id', 'reference', 'result', 'resultcode', 'payment_status'];
        $body = $this->webhookBody($payment);
        $digest = $this->sign($oldTimestamp, $fields, $body);

        config()->set('services.selcom.api_key', self::API_KEY);
        config()->set('services.selcom.api_secret', self::API_SECRET);

        $callback = $this->postJson('/api/webhooks/selcom', $body, [
            'Authorization' => 'SELCOM '.base64_encode(self::API_KEY),
            'Timestamp' => $oldTimestamp,
            'Digest-Method' => 'HS256',
            'Digest' => $digest,
            'Signed-Fields' => implode(',', $fields),
        ]);

        $callback->assertStatus(401)->assertJsonPath('error', 'timestamp_expired');

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
    }

    public function test_webhook_timestamp_too_far_in_future_is_rejected(): void
    {
        $response = $this->checkout('webhook-futurets-0001');
        $payment = $this->paymentFor($response);

        // Create a body with a future timestamp (5 minutes ahead)
        $futureTimestamp = now()->addMinutes(5)->format('Y-m-d\TH:i:sP');
        $fields = ['transid', 'order_id', 'reference', 'result', 'resultcode', 'payment_status'];
        $body = $this->webhookBody($payment);
        $digest = $this->sign($futureTimestamp, $fields, $body);

        config()->set('services.selcom.api_key', self::API_KEY);
        config()->set('services.selcom.api_secret', self::API_SECRET);

        $callback = $this->postJson('/api/webhooks/selcom', $body, [
            'Authorization' => 'SELCOM '.base64_encode(self::API_KEY),
            'Timestamp' => $futureTimestamp,
            'Digest-Method' => 'HS256',
            'Digest' => $digest,
            'Signed-Fields' => implode(',', $fields),
        ]);

        $callback->assertStatus(401)->assertJsonPath('error', 'timestamp_expired');

        $payment->refresh();
        $this->assertSame('pending', $payment->status->value);
    }

    public function test_replay_attack_with_same_transid_is_blocked(): void
    {
        $response = $this->checkout('webhook-replay-0001');
        $payment = $this->paymentFor($response);
        $body = $this->webhookBody($payment);

        // First request succeeds
        $this->postWebhook($body)->assertOk();

        // Second request with same transid should be blocked by replay protection
        $callback = $this->postWebhook($body);
        $callback->assertOk()->assertJsonPath('duplicate', true);

        // Should not create duplicate inventory movements
        $this->assertDatabaseCount('wear_inventory_movements', 2); // 1 reservation + 1 sale
        $this->assertSame('paid', $payment->fresh()->status->value);
    }

    public function test_duplicate_transid_across_different_orders_is_detected(): void
    {
        $firstPayment = $this->paymentFor($this->checkout('webhook-transid-0001'));
        $secondPayment = $this->paymentFor($this->checkout('webhook-transid-0002'));

        $sharedTransid = 'TXN-SHARED-'.Str::upper(Str::random(10));

        // First order gets the transid
        $this->postWebhook($this->webhookBody($firstPayment, ['transid' => $sharedTransid]))->assertOk();

        // Second order tries to use same transid - should be detected as replay
        $callback = $this->postWebhook($this->webhookBody($secondPayment, ['transid' => $sharedTransid]));

        // Should be detected as replay and return duplicate=true
        $callback->assertOk()->assertJsonPath('duplicate', true);
        $secondPayment->refresh();
        $this->assertSame('pending', $secondPayment->status->value);
    }

    public function test_timeout_after_selcom_accepted_does_not_mark_failed_triggers_reconciliation(): void
    {
        $response = $this->checkout('webhook-timeout-0001');
        $payment = $this->paymentFor($response);

        // Simulate a timeout exception during initiate (Selcom accepted but we timed out)
        $payment->update([
            'status' => PaymentStatus::Pending,
            'payload' => array_merge($payment->payload ?? [], [
                'gateway_order_id' => $payment->order->order_number,
                'provider_reference' => 'SELCOM-REF-123',
            ]),
        ]);

        // Call reconcileAfterTimeout - should query provider and not mark failed
        $result = app(PaymentService::class)->reconcileAfterTimeout($payment);

        // Should not have marked as failed - should transition to ReconciliationRequired
        // since provider status is still pending/in_progress
        $this->assertSame('reconciliation_required', $result->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'pending_payment']);

        // Should have recorded reconciliation attempt
        $this->assertArrayHasKey('reconciliation_attempts', $payment->fresh()->payload);
    }

    public function test_concurrent_duplicate_callbacks_are_race_safe(): void
    {
        $response = $this->checkout('webhook-concurrent-0001');
        $payment = $this->paymentFor($response);
        $body = $this->webhookBody($payment);

        // Simulate concurrent requests by calling applyProviderStatus directly
        // with the same payment object (mimicking race condition)
        $service = app(PaymentService::class);

        $providerState = [
            'status' => 'completed',
            'transid' => $body['transid'],
            'reference' => $payment->provider_reference,
            'channel' => $body['channel'],
            'amount' => $body['amount'],
            'currency' => $body['currency'] ?? 'TZS',
            'payload' => $body,
        ];

        // First call
        $result1 = $service->applyProviderStatus($payment->fresh(), $providerState);
        $this->assertSame('paid', $result1->status->value);

        // Second concurrent call should be handled idempotently
        $result2 = $service->applyProviderStatus($payment->fresh(), $providerState);
        $this->assertSame('paid', $result2->status->value);

        // Only one inventory movement for sale
        $this->assertDatabaseCount('wear_inventory_movements', 2); // 1 reservation + 1 sale
    }

    public function test_selcom_gateway_timeout_exception_is_wrapped(): void
    {
        config()->set('services.selcom', [
            'base_url' => 'https://selcom.example.test',
            'api_key' => self::API_KEY,
            'api_secret' => self::API_SECRET,
            'vendor_id' => 'KIPANYA',
            'currency' => 'TZS',
            'redirect_url' => 'https://kipanya.example/payment/success',
            'cancel_url' => 'https://kipanya.example/payment/cancel',
            'webhook_url' => 'https://kipanya.example/api/webhooks/selcom',
            'timeout' => 1,
        ]);

        Http::fake([
            'https://selcom.example.test/*' => fn () => throw new ConnectionException('Connection timed out'),
        ]);

        $user = $this->user();
        [, $variant] = $this->product(5);
        $address = $this->address($user);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('Idempotency-Key', 'webhook-gateway-timeout-0001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id]);

        $response->assertStatus(500);

        // No payment should have been created since the gateway threw before creation
        $this->assertDatabaseCount('payment_transactions', 0);
    }
}
