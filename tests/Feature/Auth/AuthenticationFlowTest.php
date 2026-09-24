<?php

namespace Tests\Feature\Auth;

use App\Integrations\Sms\SmsGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_otp_and_registration_flow(): void
    {
        $sent = [];

        $this->app->bind(SmsGateway::class, function () use (&$sent) {
            return new class($sent) implements SmsGateway
            {
                public function __construct(private array &$sent) {}

                public function send(string $phone, string $message): void
                {
                    $this->sent[] = compact('phone', 'message');
                }
            };
        });

        $response = $this->postJson('/api/v1/auth/register/request-otp', [
            'phone' => '0712345678',
        ]);

        $response->assertOk();
        $this->assertCount(1, $sent);

        preg_match('/\b\d{6}\b/', $sent[0]['message'], $matches);
        $code = $matches[0];

        $response = $this->postJson('/api/v1/auth/register', [
            'phone' => '0712345678',
            'code' => $code,
            'name' => 'Kipanya User',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.phone', '+255712345678')
            ->assertJsonPath('user.name', 'Kipanya User')
            ->assertJsonStructure(['user'])
            ->assertJsonMissing(['token', 'token_type']);

        // phone is ciphertext at rest, so the raw column cannot be compared to
        // a literal. The blind index is the queryable representation.
        $this->assertTrue(User::query()->wherePhone('+255712345678')->exists());
        $this->assertDatabaseHas('users', [
            'name' => 'Kipanya User',
        ]);

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

    public function test_login_requires_a_valid_otp_and_revokes_previous_tokens(): void
    {
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);

        $oldToken = $user->createToken('old')->plainTextToken;

        $sent = [];

        $this->app->bind(SmsGateway::class, function () use (&$sent) {
            return new class($sent) implements SmsGateway
            {
                public function __construct(private array &$sent) {}

                public function send(string $phone, string $message): void
                {
                    $this->sent[] = compact('phone', 'message');
                }
            };
        });

        $this->postJson('/api/v1/auth/login/request-otp', [
            'phone' => '+255712345678',
        ])->assertOk();

        preg_match('/\b\d{6}\b/', $sent[0]['message'], $matches);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '0712345678',
            'code' => $matches[0],
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user'])
            ->assertJsonMissing(['token', 'token_type']);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($oldToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
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
