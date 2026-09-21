<?php

namespace Tests\Feature\Foundation;

use App\Actions\Auth\IssueSanctumToken;
use App\Enums\Auth\OtpPurpose;
use App\Enums\Auth\UserRole;
use App\Integrations\Sms\SmsGateway;
use App\Models\Administration\Permission;
use App\Models\Administration\Role;
use App\Models\Cart\Cart;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearOrderItem;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\Wear\WearReturnRequest;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

/**
 * Regression coverage for the full security audit:
 *  - the last active super administrator cannot be demoted or deactivated
 *    through the user update endpoint (previously only toggleStatus guarded it);
 *  - deactivating an administrator revokes their API tokens;
 *  - stored image paths cannot traverse out of the public disk;
 *  - the storefront CTA rejects backslash-authority bypasses;
 *  - settings reject non-timezone values;
 *  - recording a refund requires the payments permission;
 *  - order status changes are audited.
 */
class SecurityAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_super_admin_cannot_be_demoted_through_update(): void
    {
        $actor = $this->userWithPermissions(['users.manage']);
        $target = User::factory()->admin()->create();
        $support = Role::query()->where('slug', 'support')->firstOrFail();

        $this->assertSame(1, $this->activeSuperAdminCount());

        $this->actingAs($actor)
            ->put(route('admin.users.update', $target), [
                'name' => 'Demotion Attempt',
                'phone' => $target->phone,
                'email' => $target->email,
                'role_id' => $support->id,
                'status' => 'active',
            ])
            // F-04: a non-super `users.manage` holder is refused outright.
            ->assertForbidden();

        $this->assertTrue($target->fresh()->hasRole('super_admin'));
        $this->assertSame(1, $this->activeSuperAdminCount());
    }

    public function test_deactivating_an_admin_through_update_revokes_token(): void
    {
        $actor = User::factory()->admin()->create();
        $target = $this->staffAdmin();
        $target->createToken('regression-test');

        $this->assertSame(1, $target->tokens()->count());

        $this->actingAs($actor)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'phone' => $target->phone,
                'email' => $target->email,
                'role_id' => $target->roles()->value('id'),
                'status' => 'inactive',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'inactive']);
        $this->assertSame(0, $target->tokens()->count());
    }

    public function test_toggling_an_admin_inactive_revokes_token(): void
    {
        $actor = User::factory()->admin()->create();
        $target = $this->staffAdmin();
        $target->createToken('regression-test');

        $this->actingAs($actor)
            ->post(route('admin.users.status', $target))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $target->tokens()->count());
    }

    public function test_changing_an_admin_credentials_through_update_revokes_token(): void
    {
        // F-04: only a super administrator may change an admin's login credentials.
        $actor = User::factory()->admin()->create();
        $target = $this->staffAdmin();
        $target->createToken('regression-test');

        $this->assertSame(1, $target->tokens()->count());

        $this->actingAs($actor)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'phone' => '+255700000001',
                'email' => 'credentials-changed@example.com',
                'role_id' => $target->roles()->value('id'),
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'phone' => '+255700000001',
            'email' => 'credentials-changed@example.com',
        ]);
        $this->assertSame(0, $target->refresh()->tokens()->count());
    }

    public function test_product_image_path_traversal_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.wear.products.store'), [
                'name' => 'Traversal Tee',
                'slug' => 'traversal-tee',
                'category' => 'T-Shirts',
                'price' => '15000',
                'sort_order' => '0',
                'image_path' => '/storage/../.env',
            ])
            ->assertSessionHasErrors('image_path');

        $this->assertDatabaseMissing('wear_products', ['slug' => 'traversal-tee']);
    }

    public function test_collection_cover_path_traversal_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.wear.collections.store'), [
                'name' => 'Traversal Collection',
                'slug' => 'traversal-collection',
                'sort_order' => '0',
                'cover_path' => '/storage/../../.env',
            ])
            ->assertSessionHasErrors('cover_path');

        $this->assertDatabaseMissing('wear_collections', ['slug' => 'traversal-collection']);
    }

    public function test_storefront_cta_rejects_backslash_url(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.storefront.update'), [
                'hero_eyebrow' => 'NEW DROP',
                'hero_title' => 'Built for your everyday.',
                'hero_description' => 'Fresh pieces.',
                'hero_cta_label' => 'Explore',
                'hero_cta_url' => '/\evil.example.test',
                'shop_default_sort' => 'featured',
            ])
            ->assertSessionHasErrors('hero_cta_url');
    }

    public function test_settings_reject_invalid_timezone(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'store_name' => 'Kipanya Wear',
                'currency' => 'TZS',
                'timezone' => 'Not/AZone',
            ])
            ->assertSessionHasErrors('timezone');
    }

    public function test_return_refund_requires_payments_permission(): void
    {
        $actor = $this->userWithPermissions(['commerce.manage']);
        $customer = User::factory()->create(['status' => 'active']);
        $order = WearOrder::factory()->create(['user_id' => $customer->id]);

        $return = WearReturnRequest::create([
            'wear_order_id' => $order->id,
            'user_id' => $customer->id,
            'request_type' => 'refund',
            'reason' => 'damaged',
            'order_item_ids' => [],
            'status' => 'processed',
        ]);

        $this->actingAs($actor)
            ->post(route('admin.wear.returns.refund', $return), ['refund_reference' => 'REF-1'])
            ->assertForbidden();
    }

    public function test_admin_order_status_change_is_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $order = WearOrder::factory()->create([
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.wear.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'admin.wear.order.status_changed',
            'auditable_id' => $order->id,
        ]);
    }

    public function test_inactive_user_bearer_token_is_rejected_everywhere(): void
    {
        $user = User::factory()->create(['status' => 'active'])->fresh();
        $token = $user->createToken('regression-test', ['auth', 'account'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();
        $this->withToken($token)->getJson('/api/v1/account/preferences')->assertOk();

        $user->forceFill(['status' => 'inactive'])->save();

        // Sanctum's request guard is cached for the lifetime of the test
        // process, so force a fresh bearer-token resolution against the DB.
        Auth::forgetGuards();

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertForbidden();
        $this->withToken($token)->getJson('/api/v1/account/preferences')->assertForbidden();
    }

    public function test_otp_verification_is_locked_out_after_repeated_failures(): void
    {
        $gateway = new class implements SmsGateway
        {
            public array $messages = [];

            public function send(string $phone, string $message): void
            {
                $this->messages[] = $message;
            }
        };
        $this->app->instance(SmsGateway::class, $gateway);

        $service = $this->app->make(OtpService::class);
        $service->send('+255712345678', OtpPurpose::Login);
        preg_match('/\b\d{6}\b/', $gateway->messages[0], $matches);
        $code = $matches[0];
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($i = 0; $i < OtpService::MAX_VERIFY_FAILURES; $i++) {
            try {
                $service->verify('+255712345678', OtpPurpose::Login, $wrong);
            } catch (ValidationException) {
                // Expected per failed attempt.
            }
        }

        // The correct code must now be rejected: the account is temporarily locked.
        $this->expectException(ValidationException::class);
        $service->verify('+255712345678', OtpPurpose::Login, $code);
    }

    public function test_expired_guest_cart_is_rotated_instead_of_reused(): void
    {
        $token = bin2hex(random_bytes(32));
        $old = Cart::create([
            'guest_token_hash' => hash('sha256', $token),
            'status' => 'active',
            'expires_at' => now()->subDay(),
        ]);

        $this->withHeader('X-Guest-Cart-Token', $token)
            ->getJson('/api/v1/cart')
            ->assertSuccessful();

        $this->assertNull(Cart::find($old->id));
        $this->assertSame(1, Cart::where('guest_token_hash', hash('sha256', $token))->count());
        $fresh = Cart::where('guest_token_hash', hash('sha256', $token))->first();
        $this->assertGreaterThan(now(), $fresh->expires_at);
    }

    public function test_issued_api_tokens_carry_the_configured_prefix(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = $user->createToken('regression-test')->plainTextToken;

        $this->assertStringContainsString('|kp_', $token);
    }

    public function test_cart_item_update_no_longer_requires_variant_id(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $product = WearProduct::factory()->create(['is_active' => true]);
        $variant = WearProductVariant::factory()->create([
            'wear_product_id' => $product->id,
            'stock' => 10,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/cart/items/{$variant->id}", ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('data.item_count', 2);
    }

    public function test_login_sets_an_http_only_same_site_lax_web_session_cookie(): void
    {
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);

        $sent = [];
        $this->app->bind(SmsGateway::class, function () use (&$sent) {
            return new class($sent) implements SmsGateway
            {
                public function __construct(public array &$sent) {}

                public function send(string $phone, string $message): void
                {
                    $this->sent[] = compact('phone', 'message');
                }
            };
        });

        $this->postJson('/api/v1/auth/login/request-otp', ['phone' => '+255712345678'])->assertOk();
        preg_match('/\b\d{6}\b/', $sent[0]['message'], $matches);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '0712345678',
            'code' => $matches[0],
        ])->assertOk();

        $cookieHeader = $response->headers->get('set-cookie');
        $this->assertNotNull($cookieHeader);
        $this->assertStringContainsString('kp_web_session=', $cookieHeader);
        $this->assertMatchesRegularExpression('/httponly/i', $cookieHeader);
        $this->assertMatchesRegularExpression('/samesite=lax/i', $cookieHeader);
        $this->assertNull($response->json('token'));
        $this->assertNull($response->json('token_type'));

        preg_match('/kp_web_session=([^;]+)/', $cookieHeader, $cookieMatch);
        $cookie = rawurldecode($cookieMatch[1]);

        // The HttpOnly cookie alone (no Authorization header) must authenticate
        // the API just like an external bearer token. JSON helper requests
        // never forward cookies, so use a plain request instead.
        $this->withUnencryptedCookies(['kp_web_session' => $cookie])
            ->withHeader('Accept', 'application/json')
            ->get('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.phone', '+255712345678');
    }

    public function test_logout_forgets_the_web_session_cookie(): void
    {
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);

        $sent = [];
        $this->app->bind(SmsGateway::class, function () use (&$sent) {
            return new class($sent) implements SmsGateway
            {
                public function __construct(public array &$sent) {}

                public function send(string $phone, string $message): void
                {
                    $this->sent[] = compact('phone', 'message');
                }
            };
        });

        $this->postJson('/api/v1/auth/login/request-otp', ['phone' => '+255712345678'])->assertOk();
        preg_match('/\b\d{6}\b/', $sent[0]['message'], $matches);

        $login = $this->postJson('/api/v1/auth/login', [
            'phone' => '0712345678',
            'code' => $matches[0],
        ])->assertOk();

        preg_match('/kp_web_session=([^;]+)/', $login->headers->get('set-cookie'), $cookieMatch);
        $cookie = rawurldecode($cookieMatch[1]);

        $this->withUnencryptedCookies(['kp_web_session' => $cookie])
            ->withHeader('Accept', 'application/json')
            ->get('/api/v1/auth/me')
            ->assertOk();

        // F-07: a browser sends Sec-Fetch-Site: same-origin on its own site's requests.
        $logout = $this->withUnencryptedCookies(['kp_web_session' => $cookie])
            ->withHeader('Accept', 'application/json')
            ->withHeader('Sec-Fetch-Site', 'same-origin')
            ->post('/api/v1/auth/logout')
            ->assertOk();

        $this->assertMatchesRegularExpression('/kp_web_session=deleted/i', (string) $logout->headers->get('set-cookie'));

        $this->withUnencryptedCookies(['kp_web_session' => $cookie])
            ->withHeader('Accept', 'application/json')
            ->get('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_storefront_pages_render_signed_in_state_from_the_web_session_cookie(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = (new IssueSanctumToken)->execute($user);

        $this->get('/account')
            ->assertOk()
            ->assertSee('name="kp-signed-in" content="0"', false);

        $this->withUnencryptedCookies(['kp_web_session' => $token])
            ->get('/account')
            ->assertOk()
            ->assertSee('name="kp-signed-in" content="1"', false);
    }

    public function test_web_session_tokens_are_scoped_not_wildcard(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = (new IssueSanctumToken)->execute($user);

        $record = PersonalAccessToken::findToken($token);

        $this->assertNotNull($record);
        $this->assertSame(['auth', 'account', 'commerce', 'cart'], $record->abilities);
        $this->assertNotContains('*', $record->abilities);
    }

    public function test_wildcard_token_is_rejected_from_scoped_endpoints(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $wildcard = $user->createToken('legacy-wildcard', ['*'])->plainTextToken;

        $this->withToken($wildcard)
            ->getJson('/api/v1/account/preferences')
            ->assertForbidden();
    }

    public function test_narrow_token_is_blocked_from_out_of_scope_endpoints(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $narrow = $user->createToken('narrow-account', ['account'])->plainTextToken;

        $this->withToken($narrow)->getJson('/api/v1/account/preferences')->assertOk();
        $this->withToken($narrow)->getJson('/api/v1/auth/me')->assertForbidden();
        $this->withToken($narrow)->getJson('/api/v1/orders')->assertForbidden();
    }

    public function test_return_requests_are_windowed_and_terminal_after_rejection(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = (new IssueSanctumToken)->execute($user);

        $makeOrder = function (string $number, int $daysAgo, string $paymentStatus = 'paid') use ($user): array {
            $product = WearProduct::create([
                'name' => 'Return Tee '.$number,
                'slug' => 'return-tee-'.strtolower($number),
                'price' => 25000,
                'category' => 'shirts',
                'is_active' => true,
            ]);
            $variant = WearProductVariant::create([
                'wear_product_id' => $product->id,
                'size' => 'M',
                'color' => 'Black',
                'stock' => 1,
                'sku' => 'RET-'.strtoupper(bin2hex(random_bytes(4))),
            ]);
            $order = WearOrder::create([
                'order_number' => $number,
                'checkout_idempotency_key' => 'ret-'.strtolower($number),
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_phone' => '+255712345678',
                'delivery_address' => 'Kipanya Street, Dar es Salaam',
                'delivery_city' => 'Dar es Salaam',
                'status' => 'delivered',
                'payment_status' => $paymentStatus,
                'subtotal' => 25000,
                'delivery_fee' => 0,
                'total' => 25000,
                'placed_at' => now()->subDays($daysAgo),
                'delivered_at' => now()->subDays($daysAgo),
            ]);
            $item = WearOrderItem::create([
                'wear_order_id' => $order->id,
                'wear_product_id' => $product->id,
                'wear_product_variant_id' => $variant->id,
                'product_name' => 'Return Tee '.$number,
                'sku' => $variant->sku,
                'size' => 'M',
                'color' => 'Black',
                'quantity' => 1,
                'unit_price' => 25000,
                'line_total' => 25000,
            ]);

            return [$order, $item];
        };

        [$fresh, $freshItem] = $makeOrder('RET-BOUND-1', 3);

        // Inside the window a request is accepted...
        $this->withToken($token)
            ->postJson('/api/v1/returns', [
                'order_id' => $fresh->id,
                'request_type' => 'return',
                'reason' => 'wrong_size',
                'item_ids' => [$freshItem->id],
            ])
            ->assertCreated();

        // ...and a rejected request is terminal: the same items cannot be re-requested.
        WearReturnRequest::query()->where('wear_order_id', $fresh->id)->update(['status' => 'rejected']);
        $this->withToken($token)
            ->postJson('/api/v1/returns', [
                'order_id' => $fresh->id,
                'request_type' => 'return',
                'reason' => 'damaged',
                'item_ids' => [$freshItem->id],
            ])
            ->assertStatus(422);

        // Orders outside the return window are neither listed nor accepted.
        [$old, $oldItem] = $makeOrder('RET-OLD-2', 31);
        $this->withToken($token)->getJson('/api/v1/returns/eligible-orders')
            ->assertJsonMissing(['order_number' => $old->order_number]);
        $this->withToken($token)
            ->postJson('/api/v1/returns', [
                'order_id' => $old->id,
                'request_type' => 'return',
                'reason' => 'wrong_size',
                'item_ids' => [$oldItem->id],
            ])
            ->assertStatus(422);

        // Fully refunded orders are not eligible either.
        [$refunded, $refundedItem] = $makeOrder('RET-REF-3', 2, 'refunded');
        $this->withToken($token)->getJson('/api/v1/returns/eligible-orders')
            ->assertJsonMissing(['order_number' => $refunded->order_number]);
        $this->withToken($token)
            ->postJson('/api/v1/returns', [
                'order_id' => $refunded->id,
                'request_type' => 'return',
                'reason' => 'wrong_size',
                'item_ids' => [$refundedItem->id],
            ])
            ->assertStatus(422);
    }

    private function staffAdmin(): User
    {
        $user = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);
        $user->roles()->sync([Role::query()->where('slug', 'support')->value('id')]);

        return $user->fresh();
    }

    private function userWithPermissions(array $slugs): User
    {
        $role = Role::query()->where('slug', 'support')->firstOrFail();
        $role->permissions()->syncWithoutDetaching(
            Permission::query()->whereIn('slug', $slugs)->pluck('id'),
        );

        $user = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);
        $user->roles()->sync([$role->id]);

        return $user->fresh();
    }

    private function activeSuperAdminCount(): int
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', static fn ($query) => $query->where('slug', 'super_admin'))
            ->count();
    }
}
