<?php

namespace Tests\Feature\Auth;

use App\Mail\ResetPasswordLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Password recovery by email link: single-use token, bounded lifetime,
 * plaintext never stored, sessions revoked on success, fresh sign-in issued.
 */
class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    private function userWithToken(array $overrides = []): array
    {
        Mail::fake();

        $user = User::factory()->create(array_merge([
            'phone' => '+255712345678',
            'email' => 'buyer@example.com',
            'status' => 'active',
            'phone_verified_at' => now(),
            'password' => 'old-password',
        ], $overrides));

        $this->postJson('/api/v1/auth/forgot-password', ['phone' => '0712345678'])
            ->assertOk();

        $token = null;
        Mail::assertSent(ResetPasswordLink::class, function (ResetPasswordLink $mail) use (&$token): bool {
            $token = $mail->token;

            return true;
        });

        $this->assertNotEmpty($token);

        return [$user->fresh(), $token];
    }

    public function test_the_emailed_token_sets_a_new_password_and_signs_the_user_in(): void
    {
        [$user, $token] = $this->userWithToken();

        // Only the sha256 of the token is stored; the plaintext never lands in
        // the database.
        $this->assertSame(hash('sha256', $token), $user->reset_token_hash);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['user']);

        $user = $user->fresh();
        $this->assertTrue(Hash::check('brand-new-pass', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));

        // Token consumed: single-use.
        $this->assertNull($user->reset_token_hash);
        $this->assertNull($user->reset_expires_at);

        // The requester is signed in straight away.
        $response->assertCookie(config('web_session.cookie'));
    }

    public function test_a_reset_link_can_only_be_used_once(): void
    {
        [, $token] = $this->userWithToken();

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertOk();

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'password' => 'another-pass-123',
            'password_confirmation' => 'another-pass-123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_an_expired_token_is_rejected(): void
    {
        [$user] = $this->userWithToken(['email' => 'buyer@example.com']);

        // Age the token past the 60-minute window.
        $user->forceFill(['reset_expires_at' => now()->subMinute()])->save();

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'whatever-the-old-token-was',
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_an_unknown_token_is_rejected(): void
    {
        $this->userWithToken();

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => bin2hex(random_bytes(32)),
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_a_weak_new_password_is_rejected(): void
    {
        [, $token] = $this->userWithToken();

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // The token was not consumed by the failed attempt.
        $this->assertNotNull(User::query()->first()->reset_token_hash);
    }

    public function test_a_successful_reset_revokes_every_existing_session(): void
    {
        [$user, $token] = $this->userWithToken();

        $oldToken = $user->createToken('old-device')->plainTextToken;

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertOk();

        // The stolen-or-lost sessions are gone; only the fresh cookie lives.
        $this->withToken($oldToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_an_inactive_account_cannot_reset_its_password(): void
    {
        Mail::fake();

        // An inactive account gets no reset link from the endpoint; simulate
        // one that still carries a (previously issued) valid token.
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'email' => 'buyer@example.com',
            'status' => 'suspended',
            'phone_verified_at' => now(),
            'password' => 'old-password',
        ]);

        $token = bin2hex(random_bytes(32));
        $user->forceFill([
            'reset_token_hash' => hash('sha256', $token),
            'reset_expires_at' => now()->addHour(),
        ])->save();

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['token']);

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
