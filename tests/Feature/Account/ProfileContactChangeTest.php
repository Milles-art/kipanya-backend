<?php

namespace Tests\Feature\Account;

use App\Integrations\Sms\SmsGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileContactChangeTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, array{phone: string, message: string}> */
    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sent = [];
        $sent = &$this->sent;

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
    }

    private function user(string $phone = '+255712345678', ?string $email = null): User
    {
        return User::factory()->create([
            'phone' => $phone,
            'email' => $email,
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
    }

    private function lastCode(): string
    {
        preg_match('/\b\d{6}\b/', end($this->sent)['message'], $m);

        return $m[0];
    }

    public function test_phone_change_request_and_verify_updates_the_number(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/phone/request', ['phone' => '+255713000001'])
            ->assertOk();

        $code = $this->lastCode();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/phone/verify', [
                'phone' => '+255713000001',
                'code' => $code,
            ])
            ->assertOk()
            ->assertJsonPath('data.phone', '+255713000001');

        $this->assertTrue(User::query()->wherePhone('+255713000001')->whereKey($user->id)->exists());
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_phone_change_rejects_a_taken_number(): void
    {
        $user = $this->user('+255712345678');
        $this->user('+255714000002');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/phone/request', ['phone' => '+255714000002'])
            ->assertStatus(422);
    }

    public function test_phone_change_rejects_a_wrong_code(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/phone/request', ['phone' => '+255713000001'])
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/phone/verify', [
                'phone' => '+255713000001',
                'code' => '000000',
            ])
            ->assertStatus(422);

        $this->assertSame('+255712345678', $user->fresh()->phone);
    }

    public function test_email_change_request_and_verify_updates_the_address(): void
    {
        $user = $this->user('+255715000003', null);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/email/request', ['email' => 'new@example.com'])
            ->assertOk();

        // The code goes to the verified phone, not the new email.
        $this->assertSame('+255715000003', end($this->sent)['phone']);
        $code = $this->lastCode();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/email/verify', [
                'email' => 'new@example.com',
                'code' => $code,
            ])
            ->assertOk()
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertTrue(User::query()->whereEmail('new@example.com')->whereKey($user->id)->exists());
    }

    public function test_email_change_rejects_a_taken_address(): void
    {
        $user = $this->user('+255712345678', 'user@example.com');
        $this->user('+255714000002', 'taken@example.com');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/account/profile/email/request', ['email' => 'taken@example.com'])
            ->assertStatus(422);
    }

    public function test_profile_page_exposes_both_change_editors(): void
    {
        $user = $this->user();

        $html = $this->actingAs($user, 'sanctum')
            ->get('/account/profile')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-phone-change', $html);
        $this->assertStringContainsString('data-email-change', $html);
        $this->assertStringContainsString('/account/profile/phone/request', $html);
        $this->assertStringContainsString('/account/profile/email/request', $html);
    }
}
