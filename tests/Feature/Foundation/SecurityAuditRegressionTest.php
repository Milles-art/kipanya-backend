<?php

namespace Tests\Feature\Foundation;

use App\Enums\Auth\OtpPurpose;
use App\Enums\Auth\UserRole;
use App\Integrations\Sms\SmsGateway;
use App\Models\Administration\Permission;
use App\Models\Administration\Role;
use App\Models\Cart\Cart;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\Wear\WearReturnRequest;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
            ->assertSessionHasErrors('user');

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
        $token = $user->createToken('regression-test')->plainTextToken;

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
