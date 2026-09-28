<?php

namespace Tests\Feature\Auth;

use App\Integrations\Sms\SmsGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutOtpTest extends TestCase
{
    use RefreshDatabase;

    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        $sent = &$this->sent;

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
    }

    private function code(): string
    {
        preg_match('/\b\d{6}\b/', $this->sent[0]['message'], $matches);

        return $matches[0];
    }

    public function test_new_number_registers_through_checkout_otp(): void
    {
        $this->postJson('/api/v1/auth/checkout/request-code', [
            'phone' => '0712345678',
        ])->assertOk();

        $this->assertCount(1, $this->sent);

        $response = $this->postJson('/api/v1/auth/checkout/verify', [
            'phone' => '0712345678',
            'code' => $this->code(),
            'name' => 'Guest Buyer',
        ]);

        $response->assertCreated()
            ->assertJsonPath('registered', true)
            ->assertJsonPath('user.phone', '+255712345678')
            ->assertJsonPath('user.name', 'Guest Buyer');

        $this->assertTrue(User::query()->wherePhone('+255712345678')->exists());
    }

    public function test_known_number_logs_in_through_checkout_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '+255712345678',
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/auth/checkout/request-code', [
            'phone' => '+255712345678',
        ])->assertOk();

        $this->postJson('/api/v1/auth/checkout/verify', [
            'phone' => '+255712345678',
            'code' => $this->code(),
        ])->assertOk()
            ->assertJsonPath('registered', false)
            ->assertJsonPath('user.id', $user->id);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_wrong_code_is_rejected_without_revealing_registration(): void
    {
        $this->postJson('/api/v1/auth/checkout/request-code', [
            'phone' => '0712345678',
        ])->assertOk();

        $this->postJson('/api/v1/auth/checkout/verify', [
            'phone' => '0712345678',
            'code' => '000000',
            'name' => 'Guest Buyer',
        ])->assertStatus(422);

        $this->assertFalse(User::query()->wherePhone('+255712345678')->exists());
    }

    public function test_new_number_requires_a_name(): void
    {
        $this->postJson('/api/v1/auth/checkout/request-code', [
            'phone' => '0712345678',
        ])->assertOk();

        // The code stays valid: failing fast on the name never burns it.
        $this->postJson('/api/v1/auth/checkout/verify', [
            'phone' => '0712345678',
            'code' => $this->code(),
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
