<?php

namespace Tests\Feature\Auth;

use App\Mail\ResetPasswordLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Anti-farm and recovery hardening: sign-up caps per connection and per
 * device, a constant-response forgot-password endpoint (no account oracle),
 * and rate limits on the recovery endpoints themselves.
 */
class SignupHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_signup_is_capped_per_ip_per_minute(): void
    {
        config(['security.signup_ip_per_minute' => 2]);

        foreach (['0712345671', '0712345672'] as $phone) {
            $this->postJson('/api/v1/auth/register', [
                'phone' => $phone,
                'name' => 'Farm Account',
                'password' => 'secret-pass-123',
                'password_confirmation' => 'secret-pass-123',
            ])->assertCreated();
        }

        $this->postJson('/api/v1/auth/register', [
            'phone' => '0712345673',
            'name' => 'Farm Account',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_signup_is_capped_per_device_cookie_per_day(): void
    {
        config(['security.signup_device_per_day' => 2]);
        // Per-IP caps must not be what trips here.
        config(['security.signup_ip_per_minute' => 50, 'security.signup_ip_per_hour' => 50, 'security.signup_ip_per_day' => 50]);

        foreach (['0712345671', '0712345672'] as $phone) {
            $this->registerWithDevice($phone, 'device-abc')->assertCreated();
        }

        $this->registerWithDevice('0712345673', 'device-abc')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        // A different browser is unaffected.
        $this->registerWithDevice('0712345674', 'device-xyz')->assertCreated();
    }

    /**
     * JSON helper requests never forward cookies: this sends the kp_device
     * cookie on a plain request (Accept header keeps the JSON response).
     */
    private function registerWithDevice(string $phone, string $device): \Illuminate\Testing\TestResponse
    {
        return $this->withUnencryptedCookies(['kp_device' => $device])
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/auth/register', [
                'phone' => $phone,
                'name' => 'Farm Account',
                'password' => 'secret-pass-123',
                'password_confirmation' => 'secret-pass-123',
            ]);
    }

    public function test_forgot_password_is_rate_limited_per_phone(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/forgot-password', ['phone' => '0712345678'])
                ->assertOk();
        }

        $this->postJson('/api/v1/auth/forgot-password', ['phone' => '0712345678'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_reset_password_submissions_are_rate_limited_per_ip(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/auth/reset-password', [
                'token' => 'guessed-token-'.$i,
                'password' => 'secret-pass-123',
                'password_confirmation' => 'secret-pass-123',
            ])->assertStatus(422)
                ->assertJsonValidationErrors(['token']);
        }

        // The11th submission is refused by the limiter, not the token check.
        $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'guessed-token-11',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    public function test_profile_contact_otp_requests_still_carry_the_per_ip_pump_guard(): void
    {
        // The remaining SMS flows (phone/email change) sit behind the
        // otp-request limiter so they cannot be used to pump the provider.
        config(['security.otp_ip_per_minute' => 2]);

        $user = User::factory()->create(['status' => 'active', 'phone_verified_at' => now()]);

        foreach (range(1, 2) as $i) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/v1/account/profile/phone/request', []);
        }

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/phone/request', [])
            ->assertStatus(429);
    }

    public function test_forgot_password_response_is_identical_for_known_and_unknown_numbers(): void
    {
        Mail::fake();

        $unknown = $this->postJson('/api/v1/auth/forgot-password', ['phone' => '0712345678'])
            ->assertOk()
            ->json('message');

        $user = User::factory()->create([
            'phone' => '+255799999999',
            'email' => 'known@example.com',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $known = $this->postJson('/api/v1/auth/forgot-password', ['phone' => '0799999999'])
            ->assertOk()
            ->json('message');

        $this->assertSame($unknown, $known, 'The forgot-password response must not reveal whether the account exists.');

        // Only the known account got a link and a stored reset token.
        Mail::assertSent(ResetPasswordLink::class);
        $this->assertNotNull($user->fresh()->reset_token_hash);
        $this->assertNotNull($user->fresh()->reset_expires_at);
    }

    public function test_accounts_without_an_email_get_no_link_but_the_same_response(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'phone' => '+255788888888',
            'email' => null,
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/forgot-password', ['phone' => '0788888888'])
            ->assertOk();

        Mail::assertNothingSent();
        $this->assertNull($user->fresh()->reset_token_hash);
    }
}
