<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Guest checkout sign-in: one step, phone + password, no SMS codes. Known
 * numbers verify against the same lockout budget as /auth/login; new numbers
 * create a lightweight account so order ownership works unchanged.
 */
class CheckoutSigninTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_number_registers_through_checkout(): void
    {
        $response = $this->postJson('/api/v1/auth/checkout/signin', [
            'phone' => '0712345678',
            'name' => 'Guest Buyer',
            'password' => 'secret-pass-123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('registered', true)
            ->assertJsonPath('user.phone', '+255712345678')
            ->assertJsonPath('user.name', 'Guest Buyer');

        $user = User::query()->wherePhone('+255712345678')->firstOrFail();
        $this->assertTrue(Hash::check('secret-pass-123', $user->password));

        // Signed in straight away: the session cookie was set.
        $response->assertCookie(config('web_session.cookie'));
    }

    public function test_known_number_logs_in_through_checkout(): void
    {
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => 'existing-pass',
        ]);

        $this->postJson('/api/v1/auth/checkout/signin', [
            'phone' => '+255712345678',
            'name' => 'Ignored For Known Numbers',
            'password' => 'existing-pass',
        ])->assertOk()
            ->assertJsonPath('registered', false)
            ->assertJsonPath('user.id', $user->id);

        // No second account was created and the typed name was not applied.
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($user->name, User::query()->find($user->id)->name);
    }

    public function test_wrong_password_is_rejected_and_creates_nothing(): void
    {
        User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => 'existing-pass',
        ]);

        $this->postJson('/api/v1/auth/checkout/signin', [
            'phone' => '+255712345678',
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_new_number_requires_a_name(): void
    {
        $this->postJson('/api/v1/auth/checkout/signin', [
            'phone' => '0712345678',
            'password' => 'secret-pass-123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertFalse(User::query()->wherePhone('+255712345678')->exists());
    }

    public function test_password_must_be_at_least_eight_characters(): void
    {
        $this->postJson('/api/v1/auth/checkout/signin', [
            'phone' => '0712345678',
            'name' => 'Guest Buyer',
            'password' => 'short',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertFalse(User::query()->wherePhone('+255712345678')->exists());
    }

    public function test_passwordless_legacy_account_is_pointed_at_the_reset_flow(): void
    {
        // Accounts created in the SMS-code era have no password; they must
        // not be claimable at checkout, but deserve an actionable message.
        User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => null,
        ]);

        $this->postJson('/api/v1/auth/checkout/signin', [
            'phone' => '+255712345678',
            'password' => 'any-password-here',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
