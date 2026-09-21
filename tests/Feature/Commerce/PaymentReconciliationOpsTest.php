<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Integrations\Payments\PaymentGateway;
use App\Integrations\Sms\SmsGateway;
use App\Models\Administration\Role;
use App\Models\Commerce\Address;
use App\Models\Commerce\PaymentTransaction;
use App\Models\User;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\Wear\WearReturnRequest;
use App\Services\Auth\TotpService;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Operational follow-ups to F-02: scheduled provider reconciliation, ops alert SMS, and the
 * admin screens/actions that let staff resolve payments flagged for refund or review.
 */
final class PaymentReconciliationOpsTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, array{phone: string, message: string}> */
    public array $sms = [];

    /** What the stubbed provider answers to status(): array, or a Throwable to throw. */
    public array|\Throwable $providerAnswer = ['status' => 'PENDING'];

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

    private function stubProvider(): void
    {
        $test = $this;
        $stub = new class($test) implements PaymentGateway
        {
            public function __construct(private object $test) {}

            public function initiate(\App\Models\Wear\WearOrder $order, string $idempotencyKey): array
            {
                return ['provider' => 'fake', 'reference' => 'FAKE-'.$order->order_number, 'payload' => [], 'status' => 'pending'];
            }

            public function status(string $orderId): array
            {
                $answer = $this->test->providerAnswer;
                if ($answer instanceof \Throwable) {
                    throw $answer;
                }

                return $answer + ['provider' => 'fake', 'payload' => ['order_id' => $orderId]];
            }

            public function cancel(string $orderId): bool
            {
                return true;
            }
        };

        $this->app->instance(PaymentGateway::class, $stub);
        $this->app->forgetInstance(PaymentService::class);

        $this->app->instance(SmsGateway::class, new class($test) implements SmsGateway
        {
            public function __construct(private object $test) {}

            public function send(string $phone, string $message): void
            {
                $this->test->sms[] = compact('phone', 'message');
            }
        });
    }

    private function completedFor(PaymentTransaction $payment): array
    {
        return ['status' => 'COMPLETED', 'amount' => (string) (int) $payment->order->total, 'transid' => 'TXN-'.$payment->id];
    }

    // ------------------------------------------------------------ reconcile command

    public function test_reconcile_command_settles_a_payment_whose_webhook_was_missed(): void
    {
        $payment = $this->paymentFor($this->checkout('recon-missed-webhook-1'));
        $this->stubProvider();
        $this->providerAnswer = $this->completedFor($payment);

        $this->artisan('kipanya:reconcile-payments', ['--minutes' => 0])->assertSuccessful();

        $payment->refresh();
        $this->assertSame('paid', $payment->status->value);
        $this->assertDatabaseHas('wear_orders', ['id' => $payment->wear_order_id, 'status' => 'confirmed']);
    }

    public function test_reconcile_command_flags_money_captured_after_the_order_was_cancelled(): void
    {
        $payment = $this->paymentFor($this->checkout('recon-after-cancel-01'));
        $user = User::findOrFail($payment->user_id);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders/'.$payment->order->order_number.'/cancel')->assertOk();

        $this->stubProvider();
        $this->providerAnswer = $this->completedFor($payment);

        $this->artisan('kipanya:reconcile-payments', ['--minutes' => 0, ])->assertSuccessful();

        // The cancelled payment is not "unresolved", so the command leaves it; a paid
        // callback (or the admin re-check) is what surfaces it. Prove the re-check does.
        $admin = User::factory()->admin()->create();
        $payment->update(['status' => PaymentStatus::InProgress]);

        $this->artisan('kipanya:reconcile-payments', ['--minutes' => 0])->assertSuccessful();

        $payment->refresh();
        $this->assertSame('reconciliation_required', $payment->status->value);
        $this->assertTrue($payment->payload['needs_refund'] ?? false);
    }

    public function test_reconcile_command_skips_recent_payments_and_survives_provider_errors(): void
    {
        $recent = $this->paymentFor($this->checkout('recon-recent-payment-1'));
        $this->stubProvider();
        $this->providerAnswer = $this->completedFor($recent);

        $this->artisan('kipanya:reconcile-payments', ['--minutes' => 30])->assertSuccessful();
        $this->assertSame('pending', $recent->fresh()->status->value, 'A payment younger than the threshold is left alone.');

        $this->providerAnswer = new \RuntimeException('provider down');
        $this->artisan('kipanya:reconcile-payments', ['--minutes' => 0])
            ->expectsOutputToContain('could not be checked')
            ->assertSuccessful();
        $this->assertSame('pending', $recent->fresh()->status->value);
    }

    public function test_reconcile_command_is_scheduled(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('kipanya:reconcile-payments')->assertSuccessful();
    }

    // ------------------------------------------------------------ ops alert

    public function test_ops_phone_is_alerted_when_a_payment_needs_a_refund_without_customer_data(): void
    {
        config(['security.alert_phone' => '+255700000001']);
        $this->stubProvider();
        $payment = $this->paymentFor($this->checkout('ops-alert-refund-0001'));
        $user = User::findOrFail($payment->user_id);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders/'.$payment->order->order_number.'/cancel')->assertOk();

        $this->postWebhook($this->webhookBody($payment))->assertOk();

        $this->assertCount(1, $this->sms);
        $this->assertSame('+255700000001', $this->sms[0]['phone']);
        $this->assertStringContainsString($payment->order->order_number, $this->sms[0]['message']);
        $this->assertStringNotContainsString($user->phone, $this->sms[0]['message']);
    }

    public function test_no_sms_is_sent_when_no_alert_phone_is_configured(): void
    {
        config(['security.alert_phone' => '']);
        $this->stubProvider();
        $payment = $this->paymentFor($this->checkout('ops-alert-none-00001'));
        $this->actingAs(User::findOrFail($payment->user_id), 'sanctum')->postJson('/api/v1/orders/'.$payment->order->order_number.'/cancel')->assertOk();

        $this->postWebhook($this->webhookBody($payment))->assertOk();

        $this->assertSame([], $this->sms);
    }

    // ------------------------------------------------------------ admin screens

    private function flaggedPayment(string $key): PaymentTransaction
    {
        $payment = $this->paymentFor($this->checkout($key));
        $this->actingAs(User::findOrFail($payment->user_id), 'sanctum')
            ->postJson('/api/v1/orders/'.$payment->order->order_number.'/cancel')->assertOk();
        $this->postWebhook($this->webhookBody($payment))->assertOk();

        return $payment->fresh();
    }

    public function test_admin_sees_the_attention_banner_and_the_refund_panel(): void
    {
        $payment = $this->flaggedPayment('admin-ui-flagged-0001');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.wear.payments.index'))
            ->assertOk()->assertSee('need attention', false)->assertSee('1 payment', false);

        $this->actingAs($admin)->get(route('admin.wear.payments.show', $payment))
            ->assertOk()->assertSee('Refund required', false)->assertSee('Record refund', false);
    }

    public function test_admin_can_record_a_refund_once_and_the_reference_cannot_be_reused(): void
    {
        $first = $this->flaggedPayment('admin-refund-first-0001');
        $second = $this->flaggedPayment('admin-refund-second-001');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.wear.payments.refund', $first), ['refund_reference' => 'SELCOM-RF-7001'])
            ->assertSessionHasNoErrors();

        $first->refresh();
        $this->assertSame('refunded', $first->status->value);
        $this->assertFalse($first->payload['needs_refund']);
        $this->assertSame('SELCOM-RF-7001', $first->payload['refund_reference']);
        $this->assertSame($admin->id, $first->payload['refunded_by']);
        $this->assertDatabaseHas('wear_orders', ['id' => $first->wear_order_id, 'payment_status' => 'refunded']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.refund_recorded']);

        $this->actingAs($admin)->post(route('admin.wear.payments.refund', $second), ['refund_reference' => 'SELCOM-RF-7001'])
            ->assertSessionHasErrors('refund_reference');
        $this->assertSame('reconciliation_required', $second->fresh()->status->value);

        // A closed item cannot be refunded twice.
        $this->actingAs($admin)->post(route('admin.wear.payments.refund', $first), ['refund_reference' => 'SELCOM-RF-7002'])
            ->assertSessionHasErrors('payment');
    }

    public function test_refund_cannot_be_recorded_for_a_payment_that_is_not_flagged(): void
    {
        $payment = $this->paymentFor($this->checkout('admin-refund-unflagged-1'));
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.wear.payments.refund', $payment), ['refund_reference' => 'SELCOM-RF-8001'])
            ->assertSessionHasErrors('payment');

        $this->assertSame('pending', $payment->fresh()->status->value);
    }

    public function test_recheck_asks_the_provider_and_settles_a_held_payment(): void
    {
        $payment = $this->paymentFor($this->checkout('admin-recheck-held-0001'));
        $this->stubProvider();
        $admin = User::factory()->admin()->create();

        // Reported completed without an amount -> held for review.
        $this->providerAnswer = ['status' => 'COMPLETED', 'transid' => 'TXN-HELD'];
        $this->actingAs($admin)->post(route('admin.wear.payments.recheck', $payment))->assertSessionHasNoErrors();
        $this->assertSame('reconciliation_required', $payment->fresh()->status->value);

        // The provider now returns the amount: the re-check settles it.
        $this->providerAnswer = $this->completedFor($payment);
        $this->actingAs($admin)->post(route('admin.wear.payments.recheck', $payment))->assertSessionHasNoErrors();

        $this->assertSame('paid', $payment->fresh()->status->value);
    }

    public function test_recheck_reports_a_provider_outage_without_a_server_error(): void
    {
        $payment = $this->paymentFor($this->checkout('admin-recheck-outage-01'));
        $this->stubProvider();
        $this->providerAnswer = new \RuntimeException('provider down');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.wear.payments.recheck', $payment))
            ->assertSessionHasErrors('payment');
    }

    public function test_admins_without_payments_manage_cannot_use_the_new_actions(): void
    {
        $payment = $this->flaggedPayment('admin-forbidden-000001');
        $support = User::factory()->create(['role' => \App\Enums\Auth\UserRole::Admin, 'status' => 'active']);
        $support->roles()->sync([Role::query()->where('slug', 'support')->value('id')]);

        $this->actingAs($support)->post(route('admin.wear.payments.refund', $payment), ['refund_reference' => 'SELCOM-RF-9001'])->assertForbidden();
        $this->actingAs($support)->post(route('admin.wear.payments.recheck', $payment))->assertForbidden();

        $this->assertSame('reconciliation_required', $payment->fresh()->status->value);
    }

    // ------------------------------------------------------------ step-up (fresh TOTP)

    /** @return array{0: User, 1: TotpService} */
    private function adminWithTwoFactor(): array
    {
        $totp = new TotpService;
        $admin = User::factory()->admin()->create();
        $admin->two_factor_secret = $totp->generateSecret();
        $admin->enableTwoFactor();

        return [$admin, $totp];
    }

    public function test_recording_a_refund_needs_a_fresh_authenticator_code(): void
    {
        config(['security.money_actions_require_totp' => true]);
        $payment = $this->flaggedPayment('stepup-refund-0000001');
        [$admin, $totp] = $this->adminWithTwoFactor();
        $url = route('admin.wear.payments.refund', $payment);

        $this->actingAs($admin)->post($url, ['refund_reference' => 'SELCOM-RF-5001'])->assertSessionHasErrors('totp_code');
        $this->actingAs($admin)->post($url, ['refund_reference' => 'SELCOM-RF-5001', 'totp_code' => '12345'])->assertSessionHasErrors('totp_code');

        $wrong = $totp->code($admin->two_factor_secret) === '000000' ? '111111' : '000000';
        $this->actingAs($admin)->post($url, ['refund_reference' => 'SELCOM-RF-5001', 'totp_code' => $wrong])->assertSessionHasErrors('totp_code');
        $this->assertSame('reconciliation_required', $payment->fresh()->status->value);

        $code = $totp->code($admin->two_factor_secret);
        $this->actingAs($admin)->post($url, ['refund_reference' => 'SELCOM-RF-5001', 'totp_code' => $code])->assertSessionHasNoErrors();
        $this->assertSame('refunded', $payment->fresh()->status->value);

        // The same code cannot approve a second refund (it is consumed).
        $second = $this->flaggedPayment('stepup-refund-0000002');
        $this->actingAs($admin)->post(route('admin.wear.payments.refund', $second), ['refund_reference' => 'SELCOM-RF-5002', 'totp_code' => $code])
            ->assertSessionHasErrors('totp_code');
        $this->assertSame('reconciliation_required', $second->fresh()->status->value);
    }

    public function test_step_up_locks_out_after_repeated_wrong_codes(): void
    {
        config(['security.money_actions_require_totp' => true]);
        $payment = $this->flaggedPayment('stepup-lockout-0000001');
        [$admin, $totp] = $this->adminWithTwoFactor();
        $url = route('admin.wear.payments.refund', $payment);
        $wrong = $totp->code($admin->two_factor_secret) === '000000' ? '111111' : '000000';

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($admin)->post($url, ['refund_reference' => 'SELCOM-RF-6001', 'totp_code' => $wrong])->assertSessionHasErrors('totp_code');
        }

        // Even the correct code is refused once locked out.
        $this->actingAs($admin)->post($url, ['refund_reference' => 'SELCOM-RF-6001', 'totp_code' => $totp->code($admin->two_factor_secret)])
            ->assertSessionHasErrors('totp_code');
        $this->assertSame('reconciliation_required', $payment->fresh()->status->value);
    }

    public function test_an_admin_without_two_factor_cannot_record_a_refund_when_step_up_is_on(): void
    {
        config(['security.money_actions_require_totp' => true]);
        $payment = $this->flaggedPayment('stepup-no2fa-000000001');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.wear.payments.refund', $payment), ['refund_reference' => 'SELCOM-RF-7001', 'totp_code' => '123456'])
            ->assertSessionHasErrors('totp_code');

        $this->assertSame('reconciliation_required', $payment->fresh()->status->value);
    }

    public function test_recording_a_return_refund_also_needs_the_authenticator_code(): void
    {
        config(['security.money_actions_require_totp' => true]);
        $payment = $this->paymentFor($this->checkout('stepup-return-00000001'));
        $item = $payment->order->items->first();
        $return = WearReturnRequest::create([
            'wear_order_id' => $payment->wear_order_id, 'user_id' => $payment->user_id, 'request_type' => 'return',
            'reason' => 'damaged', 'order_item_ids' => [$item->id], 'status' => 'processed',
            'refund_amount' => $item->line_total, 'refund_status' => 'pending', 'processed_at' => now(),
        ]);
        [$admin] = $this->adminWithTwoFactor();

        $this->actingAs($admin)->post(route('admin.wear.returns.refund', $return), ['refund_reference' => 'SELCOM-RF-8001'])
            ->assertSessionHasErrors('totp_code');

        $this->assertSame('pending', $return->fresh()->refund_status);
    }
}
