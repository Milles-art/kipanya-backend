<?php

namespace Tests\Feature\Account;

use App\Enums\Auth\UserStatus;
use App\Models\Commerce\LoyaltyAccount;
use App\Models\Commerce\LoyaltyTransaction;
use App\Models\Commerce\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AccountAccountEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create(['status' => UserStatus::Active, 'phone_verified_at' => now()]);
    }

    public function test_loyalty_requires_authentication(): void
    {
        $this->getJson('/api/v1/loyalty')->assertUnauthorized();
    }

    public function test_loyalty_returns_account_with_zero_points_and_ledger(): void
    {
        $user = $this->user();
        $account = LoyaltyAccount::create(['user_id' => $user->id]);
        $account->earn(120, 'Order #KP-1001', ['order_number' => 'KP-1001']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/loyalty')
            ->assertOk()
            ->assertJsonPath('data.points', 120)
            ->assertJsonPath('data.lifetime_points', 120)
            ->assertJsonCount(1, 'data.transactions')
            ->assertJsonPath('data.transactions.0.type', 'earn')
            ->assertJsonPath('data.transactions.0.points', 120);

        $this->assertDatabaseHas('loyalty_accounts', ['user_id' => $user->id, 'points' => 120, 'lifetime_points' => 120]);
        $this->assertDatabaseHas('loyalty_transactions', ['loyalty_account_id' => $account->id, 'user_id' => $user->id, 'points' => 120, 'type' => 'earn']);
    }

    public function test_loyalty_creates_account_on_first_read(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/loyalty')
            ->assertOk()
            ->assertJsonPath('data.points', 0)
            ->assertJsonPath('data.transactions', []);
    }

    public function test_payment_methods_require_authentication(): void
    {
        $this->getJson('/api/v1/payment-methods')->assertUnauthorized();
    }

    public function test_payment_methods_list_and_delete_are_scoped_to_owner(): void
    {
        $user = $this->user();
        $other = $this->user();
        $mine = PaymentMethod::create(['user_id' => $user->id, 'brand' => 'Visa', 'last4' => '4242', 'exp_month' => '09', 'exp_year' => '2029', 'is_default' => true]);
        $theirs = PaymentMethod::create(['user_id' => $other->id, 'brand' => 'Mastercard', 'last4' => '1234', 'is_default' => false]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/payment-methods')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.brand', 'Visa')
            ->assertJsonPath('data.0.is_default', true);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/payment-methods/{$mine->id}")
            ->assertOk()
            ->assertJsonPath('data.deleted', true);

        $this->assertDatabaseMissing('payment_methods', ['id' => $mine->id]);
        $this->assertDatabaseHas('payment_methods', ['id' => $theirs->id]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/payment-methods/{$theirs->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('payment_methods', ['id' => $theirs->id]);
    }

    public function test_payment_methods_set_default_is_scoped_to_owner_and_clears_prior_default(): void
    {
        $user = $this->user();
        $other = $this->user();
        $mine = PaymentMethod::create(['user_id' => $user->id, 'brand' => 'Visa', 'last4' => '4242', 'exp_month' => '09', 'exp_year' => '2029', 'is_default' => true]);
        $mineSecond = PaymentMethod::create(['user_id' => $user->id, 'brand' => 'Mastercard', 'last4' => '5555', 'exp_month' => '12', 'exp_year' => '2030', 'is_default' => false]);
        $theirs = PaymentMethod::create(['user_id' => $other->id, 'brand' => 'Visa', 'last4' => '1234', 'is_default' => false]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/payment-methods/{$mineSecond->id}/set-default")
            ->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertSame(1, PaymentMethod::where('user_id', $user->id)->where('is_default', true)->count());
        $this->assertFalse($mine->fresh()->is_default);
        $this->assertTrue($mineSecond->fresh()->is_default);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/payment-methods/{$theirs->id}/set-default")
            ->assertForbidden();

        $this->assertFalse($theirs->fresh()->is_default);
    }

    public function test_update_password_requires_current_password_when_set(): void
    {
        $user = $this->user();
        // Assign via direct attribute so the model's `hashed` cast runs exactly once.
        $user->password = 'Password123!';
        $user->save();

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/password', [
                'current_password' => 'not-the-password',
                'new_password' => 'NewPassword123!',
                'new_password_confirmation' => 'NewPassword123!',
            ])
            ->assertStatus(422);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/password', [
                'current_password' => 'Password123!',
                'new_password' => 'NewPassword123!',
                'new_password_confirmation' => 'NewPassword123!',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_update_password_requires_authentication_and_confirmation(): void
    {
        $this->putJson('/api/v1/password', [])->assertUnauthorized();

        $user = $this->user();
        $user->password = 'Password123!';
        $user->save();
        $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/password', [
                'current_password' => 'Password123!',
                'new_password' => 'short',
                'new_password_confirmation' => 'different',
            ])
            ->assertStatus(422);
    }

    public function test_sessions_lists_and_revokes_own_tokens_only(): void
    {
        $user = $this->user();
        $first = $user->createToken('web-client', ['auth', 'account', 'commerce', 'cart']);
        $second = $user->createToken('mobile-app', ['auth', 'account']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/sessions')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/sessions/{$second->accessToken->id}")
            ->assertOk()
            ->assertJsonPath('data.revoked', true);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $second->accessToken->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $first->accessToken->id]);
    }

    public function test_sessions_require_authentication(): void
    {
        $this->getJson('/api/v1/sessions')->assertUnauthorized();
    }
}