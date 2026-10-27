<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_password_account_and_signs_in(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'phone' => '0712345678',
            'name' => 'Kipanya User',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'email' => 'kipanya@example.com',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.phone', '+255712345678')
            ->assertJsonPath('user.name', 'Kipanya User')
            ->assertJsonStructure(['user'])
            ->assertJsonMissing(['token', 'token_type']);

        // The password is stored as a bcrypt hash, never in plaintext.
        $user = User::query()->wherePhone('+255712345678')->firstOrFail();
        $this->assertNotNull($user->password);
        $this->assertNotSame('secret-pass-123', $user->password);
        $this->assertTrue(Hash::check('secret-pass-123', $user->password));

        // The web session travels as an HttpOnly cookie, not in the JSON body.
        $response->assertCookie(config('web_session.cookie'));

        $this->assertDatabaseHas('user_role', [
            'user_id' => $response->json('user.id'),
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $response->json('user.id'),
        ]);
        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $response->json('user.id'),
        ]);
    }

    public function test_registering_an_existing_number_is_rejected(): void
    {
        User::factory()->create(['phone' => '+255712345678']);

        $this->postJson('/api/v1/auth/register', [
            'phone' => '0712345678',
            'name' => 'Duplicate User',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertStatus(422)->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_with_the_right_password_works_and_revokes_previous_tokens(): void
    {
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => 'correct-horse',
        ]);

        $oldToken = $user->createToken('old')->plainTextToken;

        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '0712345678',
            'password' => 'correct-horse',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user'])
            ->assertJsonMissing(['token', 'token_type']);

        // Single-session policy: the old token died, only the new one lives.
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($oldToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_login_with_a_wrong_password_is_rejected_with_a_uniform_message(): void
    {
        User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => 'correct-horse',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'phone' => '0712345678',
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        // Unknown numbers get the exact same message: no account oracle.
        $this->postJson('/api/v1/auth/login', [
            'phone' => '0799999999',
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_a_passwordless_legacy_account_cannot_password_login(): void
    {
        // Accounts created in the SMS-code era have no password at all.
        User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => null,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'phone' => '0712345678',
            'password' => 'anything-at-all',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_an_inactive_account_cannot_log_in(): void
    {
        User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'suspended',
            'password' => 'correct-horse',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'phone' => '0712345678',
            'password' => 'correct-horse',
        ])->assertStatus(422);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_revokes_current_token_only(): void
    {
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);

        $token = $user->createToken('test', ['auth'])->plainTextToken;
        $tokenId = (int) explode('|', $token, 2)[0];

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenId,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }
}
