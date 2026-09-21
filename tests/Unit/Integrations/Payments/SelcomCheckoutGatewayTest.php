<?php

namespace Tests\Unit\Integrations\Payments;

use App\Enums\Auth\UserStatus;
use App\Integrations\Payments\FakePaymentGateway;
use App\Integrations\Payments\PaymentGateway;
use App\Integrations\Payments\SelcomCheckoutGateway;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SelcomCheckoutGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://selcom.example.test';

    private const API_KEY = 'selcom_test_api_key';

    private const API_SECRET = 'selcom_test_api_secret';

    private const VENDOR_ID = 'KIPANYA';

    private const REDIRECT_URL = 'https://kipanya.example/payment/success';

    private const CANCEL_URL = 'https://kipanya.example/payment/cancel';

    private const WEBHOOK_URL = 'https://kipanya.example/api/webhooks/selcom';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.selcom', [
            'base_url' => self::BASE_URL,
            'api_key' => self::API_KEY,
            'api_secret' => self::API_SECRET,
            'vendor_id' => self::VENDOR_ID,
            'currency' => 'TZS',
            'redirect_url' => self::REDIRECT_URL,
            'cancel_url' => self::CANCEL_URL,
            'webhook_url' => self::WEBHOOK_URL,
            'timeout' => 10,
        ]);

        Http::preventStrayRequests();
    }

    private function gateway(): SelcomCheckoutGateway
    {
        return app(PaymentGateway::class);
    }

    private function order(string $orderNumber = 'KP-260919-ABCD1234', int $total = 50000): WearOrder
    {
        $user = User::factory()->create(['status' => UserStatus::Active, 'phone_verified_at' => now()]);
        $product = WearProduct::create([
            'name' => 'Selcom Tee', 'slug' => 'selcom-tee-'.uniqid(), 'price' => $total,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 10, 'sku' => 'SEL-'.strtoupper(bin2hex(random_bytes(4))),
        ]);

        $order = WearOrder::factory()->create([
            'user_id' => $user->id,
            'order_number' => $orderNumber,
            'customer_email' => 'buyer@example.com',
            'customer_name' => 'Buyer Name',
            'customer_phone' => '+255712345678',
            'subtotal' => $total,
            'delivery_fee' => 0,
            'total' => $total,
        ]);

        $order->items()->create([
            'wear_product_id' => $product->id,
            'wear_product_variant_id' => $variant->id,
            'product_name' => 'Selcom Tee',
            'sku' => $variant->sku,
            'size' => 'M',
            'color' => 'Black',
            'quantity' => 1,
            'unit_price' => $total,
            'line_total' => $total,
        ]);

        return $order;
    }

    private function signedFieldsFor(Request $request): string
    {
        $parts = ['timestamp='.$request->header('Timestamp')[0]];

        foreach ($request->data() as $key => $value) {
            $parts[] = $key.'='.(string) $value;
        }

        return implode('&', $parts);
    }

    public function test_initiate_posts_create_order_minimal_with_signed_selcom_headers(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'reference' => '0289999288',
                'resultcode' => '000',
                'result' => 'SUCCESS',
                'message' => 'Payment notification logged',
                'data' => [[
                    'gateway_buyer_uuid' => 'buyer-uuid-1',
                    'payment_token' => 'tok-1',
                    'payment_gateway_url' => base64_encode('https://pay.example/launch/pg'),
                ]],
            ], 200),
        ]);

        $order = $this->order();
        $result = $this->gateway()->initiate($order, 'checkout-selcom-0001');

        Http::assertSent(function (Request $request) use ($order): bool {
            if ($request->url() !== self::BASE_URL.'/v1/checkout/create-order-minimal') {
                return false;
            }
            if ($request->method() !== 'POST') {
                return false;
            }
            if (! hash_equals('SELCOM '.base64_encode(self::API_KEY), $request->header('Authorization')[0])) {
                return false;
            }
            if (! $request->hasHeader('Digest-Method', 'HS256')) {
                return false;
            }
            if (! $request->hasHeader('Content-Type', 'application/json')) {
                return false;
            }
            if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $request->header('Timestamp')[0])) {
                return false;
            }

            $expectedFields = ['vendor', 'order_id', 'buyer_email', 'buyer_name', 'buyer_phone', 'amount', 'currency', 'redirect_url', 'cancel_url', 'webhook', 'buyer_remarks', 'merchant_remarks', 'no_of_items'];
            if ($request->header('Signed-Fields')[0] !== implode(',', $expectedFields)) {
                return false;
            }

            $expectedDigest = base64_encode(hash_hmac('sha256', $this->signedFieldsFor($request), self::API_SECRET, true));
            if (! hash_equals($expectedDigest, $request->header('Digest')[0])) {
                return false;
            }

            $payload = $request->data();
            if ($payload['vendor'] !== self::VENDOR_ID) {
                return false;
            }
            if ($payload['order_id'] !== $order->order_number) {
                return false;
            }
            if ($payload['buyer_email'] !== 'buyer@example.com') {
                return false;
            }
            if ($payload['buyer_name'] !== 'Buyer Name') {
                return false;
            }
            if ($payload['buyer_phone'] !== '255712345678') {
                return false;
            }
            if ($payload['amount'] !== '50000') {
                return false;
            }
            if ($payload['currency'] !== 'TZS') {
                return false;
            }
            if ($payload['redirect_url'] !== base64_encode(self::REDIRECT_URL)) {
                return false;
            }
            if ($payload['cancel_url'] !== base64_encode(self::CANCEL_URL)) {
                return false;
            }
            if ($payload['webhook'] !== base64_encode(self::WEBHOOK_URL)) {
                return false;
            }
            if ($payload['no_of_items'] !== '1') {
                return false;
            }

            return true;
        });

        $this->assertSame('selcom_checkout', $result['provider']);
        $this->assertSame('0289999288', $result['reference']);
        $this->assertSame('pending', $result['status']);
        $this->assertSame('https://pay.example/launch/pg', $result['payload']['payment_gateway_url']);
        $this->assertSame($order->order_number, $result['payload']['gateway_order_id']);
        $this->assertSame('buyer-uuid-1', $result['payload']['gateway_buyer_uuid']);
    }

    public function test_create_order_response_without_reference_or_gateway_url_throws(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'resultcode' => '000',
                'result' => 'SUCCESS',
                'data' => [[]],
            ], 200),
        ]);

        try {
            $this->gateway()->initiate($this->order(), 'checkout-selcom-missing');
            $this->fail('Expected a RuntimeException for the malformed create-order response.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('reference', $e->getMessage());
            $this->assertStringNotContainsString(self::API_KEY, $e->getMessage());
            $this->assertStringNotContainsString(self::API_SECRET, $e->getMessage());
        }
    }

    public function test_failed_create_order_throws_without_leaking_credentials_or_digest(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'resultcode' => '400',
                'result' => 'FAIL',
                'message' => 'Invalid request.',
            ], 400),
        ]);

        try {
            $this->gateway()->initiate($this->order(), 'checkout-selcom-fail');
            $this->fail('Expected a RuntimeException for the failed create-order request.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('HTTP 400', $e->getMessage());
            $this->assertStringNotContainsString(self::API_KEY, $e->getMessage());
            $this->assertStringNotContainsString(self::API_SECRET, $e->getMessage());
        }
    }

    public function test_server_error_throws_without_leaking_credentials(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response('', 500),
        ]);

        try {
            $this->gateway()->initiate($this->order(), 'checkout-selcom-500');
            $this->fail('Expected a RuntimeException for the server error.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('HTTP 500', $e->getMessage());
            $this->assertStringNotContainsString(self::API_KEY, $e->getMessage());
            $this->assertStringNotContainsString(self::API_SECRET, $e->getMessage());
        }
    }

    public function test_initiation_returns_pending_and_does_not_settle_the_order(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'reference' => '0289999288',
                'resultcode' => '000',
                'result' => 'SUCCESS',
                'data' => [[
                    'gateway_buyer_uuid' => 'buyer-uuid-1',
                    'payment_gateway_url' => base64_encode('https://pay.example/launch/pg'),
                ]],
            ], 200),
        ]);

        $order = $this->order();
        $result = $this->gateway()->initiate($order, 'checkout-selcom-pending');

        $this->assertSame('pending', $result['status']);
        $this->assertSame('pending_payment', $order->status->value);
    }

    public function test_selcom_gateway_is_bound_when_credentials_are_configured(): void
    {
        $this->assertInstanceOf(SelcomCheckoutGateway::class, app(PaymentGateway::class));
    }

    public function test_fake_gateway_is_bound_when_selcom_credentials_are_missing(): void
    {
        config()->set('services.selcom', [
            'base_url' => null,
            'api_key' => null,
            'api_secret' => null,
            'vendor_id' => null,
        ]);

        $this->assertInstanceOf(FakePaymentGateway::class, app(PaymentGateway::class));
    }

    public function test_blank_selcom_credentials_still_select_the_fake_gateway(): void
    {
        config()->set('services.selcom', [
            'base_url' => '   ',
            'api_key' => '   ',
            'api_secret' => '   ',
            'vendor_id' => '   ',
        ]);

        $this->assertInstanceOf(FakePaymentGateway::class, app(PaymentGateway::class));
    }

    public function test_status_queries_order_status_endpoint_with_signed_order_id(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'data' => [[
                    'order_id' => 'KP-260919-ABCD1234',
                    'payment_status' => 'COMPLETED',
                    'transid' => 'TXN-998877',
                    'reference' => '0289999288',
                    'channel' => 'TIGOPESATZ',
                    'amount' => '50000',
                ]],
            ], 200),
        ]);

        $state = $this->gateway()->status('KP-260919-ABCD1234');

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && $request->url() === self::BASE_URL.'/v1/checkout/order-status?order_id=KP-260919-ABCD1234'
                && $request->hasHeader('Signed-Fields', 'order_id')
                && hash_equals(
                    base64_encode(hash_hmac('sha256', 'timestamp='.$request->header('Timestamp')[0].'&order_id=KP-260919-ABCD1234', self::API_SECRET, true)),
                    $request->header('Digest')[0]
                );
        });

        $this->assertSame('completed', $state['status']);
        $this->assertSame('TXN-998877', $state['transid']);
        $this->assertSame('0289999288', $state['reference']);
        $this->assertSame('TIGOPESATZ', $state['channel']);
        $this->assertSame('50000', $state['amount']);
    }

    public function test_status_normalizes_all_documented_selcom_states(): void
    {
        $cases = [
            'PENDING' => 'pending',
            'INPROGRESS' => 'in_progress',
            'COMPLETED' => 'completed',
            'CANCELLED' => 'cancelled',
            'USERCANCELED' => 'user_cancelled',
            'USERCANCELLED' => 'user_cancelled',
            'REJECTED' => 'rejected',
        ];

        // Use a sequence to return different responses for each call
        $sequence = Http::fakeSequence();
        foreach ($cases as $selcomStatus => $expected) {
            $sequence->push([
                'data' => [[
                    'order_id' => 'KP-1',
                    'payment_status' => $selcomStatus,
                ]],
            ], 200);
        }

        foreach ($cases as $selcomStatus => $expected) {
            $state = $this->gateway()->status('KP-1');
            $this->assertSame($expected, $state['status'], "Selcom status {$selcomStatus} was not normalized. Got: {$state['status']}");
        }
    }

    public function test_status_unknown_status_falls_back_to_pending_without_inventing_states(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'data' => [['order_id' => 'KP-1', 'payment_status' => 'SOMETHING_ELSE']],
            ], 200),
        ]);

        $state = $this->gateway()->status('KP-1');

        $this->assertSame('pending', $state['status']);
    }

    public function test_cancel_sends_signed_cancel_order_request(): void
    {
        Http::fake([
            'https://selcom.example.test/*' => Http::response([
                'resultcode' => '000',
                'result' => 'SUCCESS',
                'message' => 'Order cancelled.',
            ], 200),
        ]);

        $cancelled = $this->gateway()->cancel('KP-260919-ABCD1234');

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'DELETE'
                && $request->url() === self::BASE_URL.'/v1/checkout/cancel-order?order_id=KP-260919-ABCD1234'
                && $request->hasHeader('Signed-Fields', 'order_id')
                && hash_equals(
                    base64_encode(hash_hmac('sha256', 'timestamp='.$request->header('Timestamp')[0].'&order_id=KP-260919-ABCD1234', self::API_SECRET, true)),
                    $request->header('Digest')[0]
                );
        });

        $this->assertTrue($cancelled);
    }
}
