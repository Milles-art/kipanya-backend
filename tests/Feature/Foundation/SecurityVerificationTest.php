<?php

namespace Tests\Feature\Foundation;

use App\Enums\Auth\UserStatus;
use App\Integrations\Sms\SmsGateway;
use App\Models\Cart\Cart;
use App\Models\Commerce\Address;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Explicit regression tests for the attack classes from the audit checklist: ownership
 * (IDOR/BOLA), guest-token lifecycle, last-unit checkout, OTP replay and purchasability.
 */
final class SecurityVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, array{phone: string, message: string}> */
    public array $sms = [];

    private function user(): User
    {
        return User::factory()->create(['status' => UserStatus::Active, 'phone_verified_at' => now()]);
    }

    private function variant(int $stock = 10, bool $active = true): WearProductVariant
    {
        $product = WearProduct::create([
            'name' => 'Verify Tee', 'slug' => 'verify-tee-'.uniqid(), 'price' => 25000,
            'category' => 'shirts', 'is_active' => $active,
        ]);

        return WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => $stock, 'sku' => 'VER-'.strtoupper(bin2hex(random_bytes(4))),
        ]);
    }

    private function address(User $user): Address
    {
        return Address::create([
            'user_id' => $user->id, 'type' => 'shipping', 'recipient_name' => 'Customer',
            'phone' => '+255712345678', 'region' => 'Dar es Salaam', 'district' => 'Kinondoni',
            'ward' => 'Mwananyamala', 'street' => 'Verify Street', 'is_default' => true,
        ]);
    }

    private function addToCart(User $user, WearProductVariant $variant, int $qty = 1): TestResponse
    {
        return $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => $qty]);
    }

    private function checkout(User $user, string $key): TestResponse
    {
        return $this->actingAs($user, 'sanctum')->withHeader('Idempotency-Key', $key)
            ->postJson('/api/v1/checkout', ['address_id' => $this->address($user)->id]);
    }

    private function orderFor(User $user, string $key): WearOrder
    {
        $this->addToCart($user, $this->variant())->assertSuccessful();

        return WearOrder::findOrFail($this->checkout($user, $key)->assertSuccessful()->json('data.id'));
    }

    // ------------------------------------------------- guest cart lifecycle

    public function test_a_guest_token_no_longer_reaches_the_old_cart_after_merge(): void
    {
        $variant = $this->variant();
        $token = bin2hex(random_bytes(32));

        $this->withHeader('X-Guest-Cart-Token', $token)
            ->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2])->assertSuccessful();

        $user = $this->user();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/cart/merge', ['guest_cart_token' => $token])
            ->assertOk()->assertJsonPath('data.item_count', 2);

        // Replaying the old token as a guest must not expose or modify the merged items.
        $again = $this->app['auth']->forgetGuards();
        $this->withHeader('X-Guest-Cart-Token', $token)->getJson('/api/v1/cart')
            ->assertSuccessful()->assertJsonPath('data.item_count', 0);

        $this->assertSame(2, (int) Cart::query()->where('user_id', $user->id)->first()->items()->sum('quantity'));
    }

    // ------------------------------------------------- last unit

    public function test_two_customers_cannot_both_buy_the_last_unit(): void
    {
        $variant = $this->variant(stock: 1);
        $a = $this->user();
        $b = $this->user();

        $this->addToCart($a, $variant)->assertSuccessful();
        $this->addToCart($b, $variant)->assertSuccessful();

        $this->checkout($a, 'last-unit-customer-a-1')->assertSuccessful();
        $this->checkout($b, 'last-unit-customer-b-1')->assertStatus(422);

        $this->assertSame(1, WearOrder::query()->count());
        $this->assertSame(1, (int) $variant->fresh()->stock, 'Stock is only consumed when the payment is confirmed.');
        $this->assertSame(1, (int) \DB::table('wear_stock_reservation_items')->sum('quantity'), 'Exactly one unit is reserved.');
    }

    public function test_cancelling_an_order_returns_the_unit_to_other_customers(): void
    {
        $variant = $this->variant(stock: 1);
        $a = $this->user();
        $b = $this->user();
        $this->addToCart($a, $variant)->assertSuccessful();
        $this->addToCart($b, $variant)->assertSuccessful();

        $order = WearOrder::findOrFail($this->checkout($a, 'release-on-cancel-a-001')->assertSuccessful()->json('data.id'));
        $this->actingAs($a, 'sanctum')->postJson('/api/v1/orders/'.$order->order_number.'/cancel')->assertOk();

        $this->checkout($b, 'release-on-cancel-b-001')->assertSuccessful();
    }

    // ------------------------------------------------- ownership matrix

    public function test_a_customer_cannot_view_or_cancel_another_customers_order(): void
    {
        $owner = $this->user();
        $attacker = $this->user();
        $order = $this->orderFor($owner, 'idor-order-owner-000001');

        $show = $this->actingAs($attacker, 'sanctum')->getJson('/api/v1/orders/'.$order->order_number);
        $cancel = $this->actingAs($attacker, 'sanctum')->postJson('/api/v1/orders/'.$order->order_number.'/cancel');

        $this->assertContains($show->status(), [403, 404]);
        $this->assertContains($cancel->status(), [403, 404]);
        $this->assertSame('pending_payment', $order->fresh()->status->value);
    }

    public function test_a_customer_cannot_change_or_delete_another_customers_address(): void
    {
        $owner = $this->user();
        $attacker = $this->user();
        $address = $this->address($owner);

        $update = $this->actingAs($attacker, 'sanctum')->putJson('/api/v1/addresses/'.$address->id, [
            'type' => 'shipping', 'recipient_name' => 'Attacker', 'phone' => '+255712345678',
            'region' => 'Dar es Salaam', 'district' => 'Kinondoni', 'ward' => 'Mwananyamala', 'street' => 'Hijacked',
        ]);
        $delete = $this->actingAs($attacker, 'sanctum')->deleteJson('/api/v1/addresses/'.$address->id);

        $this->assertContains($update->status(), [403, 404]);
        $this->assertContains($delete->status(), [403, 404]);
        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'street' => 'Verify Street']);
    }

    public function test_a_customer_cannot_check_out_with_someone_elses_address(): void
    {
        $owner = $this->user();
        $attacker = $this->user();
        $address = $this->address($owner);
        $this->addToCart($attacker, $this->variant())->assertSuccessful();

        $this->actingAs($attacker, 'sanctum')->withHeader('Idempotency-Key', 'foreign-address-000001')
            ->postJson('/api/v1/checkout', ['address_id' => $address->id])
            ->assertStatus(422);

        $this->assertSame(0, WearOrder::query()->count());
    }

    public function test_wishlist_removal_only_affects_the_callers_own_list(): void
    {
        $a = $this->user();
        $b = $this->user();
        $product = $this->variant()->product;

        $this->actingAs($a, 'sanctum')->postJson('/api/v1/wishlist', ['product_id' => $product->id])->assertSuccessful();
        $this->actingAs($b, 'sanctum')->deleteJson('/api/v1/wishlist/'.$product->id);

        $this->actingAs($a, 'sanctum')->getJson('/api/v1/wishlist')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_a_customer_cannot_change_a_line_that_only_exists_in_another_cart(): void
    {
        $a = $this->user();
        $b = $this->user();
        $variant = $this->variant();
        $this->addToCart($a, $variant, 2)->assertSuccessful();

        $this->actingAs($b, 'sanctum')->putJson('/api/v1/cart/items/'.$variant->id, ['quantity' => 9]);
        $this->actingAs($b, 'sanctum')->deleteJson('/api/v1/cart/items/'.$variant->id);

        $this->assertSame(2, (int) Cart::query()->where('user_id', $a->id)->first()->items()->sum('quantity'));
    }

    // ------------------------------------------------- OTP replay

    public function test_a_consumed_login_code_cannot_be_used_again(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        config(['auth.expose_otp_codes' => false]);

        $test = $this;
        $this->app->instance(SmsGateway::class, new class($test) implements SmsGateway
        {
            public function __construct(private object $test) {}

            public function send(string $phone, string $message): void
            {
                $this->test->sms[] = compact('phone', 'message');
            }
        });

        $user = $this->user();
        $this->postJson('/api/v1/auth/login/request-otp', ['phone' => $user->phone])->assertOk();
        preg_match('/\b\d{6}\b/', end($this->sms)['message'], $m);

        $this->postJson('/api/v1/auth/login', ['phone' => $user->phone, 'code' => $m[0]])->assertOk();
        $this->postJson('/api/v1/auth/login', ['phone' => $user->phone, 'code' => $m[0]])->assertStatus(422);
    }

    // ------------------------------------------------- purchasability

    public function test_an_inactive_product_cannot_be_added_to_a_cart(): void
    {
        $variant = $this->variant(active: false);

        $response = $this->addToCart($this->user(), $variant);

        $this->assertGreaterThanOrEqual(400, $response->status());
        $this->assertSame(0, (int) \DB::table('cart_items')->count());
    }

    public function test_a_product_deactivated_after_carting_cannot_be_checked_out(): void
    {
        $user = $this->user();
        $variant = $this->variant();
        $this->addToCart($user, $variant)->assertSuccessful();

        $variant->product->update(['is_active' => false]);

        $this->checkout($user, 'deactivated-product-0001')->assertStatus(422);
        $this->assertSame(0, WearOrder::query()->count());
    }
}
