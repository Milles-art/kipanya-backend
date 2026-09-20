<?php

namespace Tests\Feature\Auth;

use App\Integrations\Sms\SmsGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * F-05 (SMS-pumping ceilings) and F-06 (no account-existence oracle on OTP requests).
 */
final class OtpRequestHardeningTest extends TestCase
{
    use RefreshDatabase;

    private const KNOWN = '+255712345678';

    private const UNKNOWN = '+255755555555';

    /** @var array<int, array{phone: string, message: string}> */
    public array $sent = [];

    public bool $gatewayFails = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Production behaviour: no dev OTP in responses.
        config(['auth.expose_otp_codes' => false, 'auth.log_otp_codes' => false]);

        $test = $this;
        $this->app->instance(SmsGateway::class, new class($test) implements SmsGateway
        {
            public function __construct(private object $test) {}

            public function send(string $phone, string $message): void
            {
                if ($this->test->gatewayFails) {
                    throw new \RuntimeException('provider down');
                }
                $this->test->sent[] = compact('phone', 'message');
            }
        });
    }

    private function member(): User
    {
        return User::factory()->create(['phone' => self::KNOWN, 'phone_verified_at' => now(), 'status' => 'active']);
    }

    // ------------------------------------------------------------------ F-05

    public function test_per_ip_minute_ceiling_applies_even_across_different_phone_numbers(): void
    {
        config(['security.otp_ip_per_minute' => 3]);

        foreach (['+255711000001', '+255711000002', '+255711000003'] as $phone) {
            $this->postJson('/api/v1/auth/login/request-otp', ['phone' => $phone])->assertOk();
        }

        $this->postJson('/api/v1/auth/login/request-otp', ['phone' => '+255711000004'])->assertStatus(429);
    }

    public function test_global_daily_ceiling_blocks_a_distributed_pumping_attempt(): void
    {
        config(['security.otp_global_per_day' => 2]);

        foreach (['10.0.0.1', '10.0.0.2'] as $ip) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->postJson('/api/v1/auth/login/request-otp', ['phone' => '+255711000010'])->assertOk();
        }

        // A third request from a brand-new IP is still refused: the global ceiling is hit.
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
            ->postJson('/api/v1/auth/login/request-otp', ['phone' => '+255711000011'])->assertStatus(429);
    }

    // ------------------------------------------------------------------ F-06

    public function test_login_otp_response_is_identical_for_known_and_unknown_numbers(): void
    {
        $this->member();

        $known = $this->postJson('/api/v1/auth/login/request-otp', ['phone' => self::KNOWN]);
        $unknown = $this->postJson('/api/v1/auth/login/request-otp', ['phone' => self::UNKNOWN]);

        $known->assertOk();
        $unknown->assertOk();
        $this->assertSame($known->getContent(), $unknown->getContent());

        // ...but only the real account actually receives an SMS.
        $this->assertCount(1, $this->sent);
        $this->assertSame(self::KNOWN, $this->sent[0]['phone']);
    }

    public function test_cooldown_no_longer_reveals_that_an_account_exists(): void
    {
        $this->member();

        $first = $this->postJson('/api/v1/auth/login/request-otp', ['phone' => self::KNOWN]);
        // Immediately again: OtpService's 60-second cooldown used to answer 422 here.
        $second = $this->postJson('/api/v1/auth/login/request-otp', ['phone' => self::KNOWN]);

        $second->assertOk();
        $this->assertSame($first->getContent(), $second->getContent());
        $this->assertCount(1, $this->sent, 'The cooldown must still suppress the second SMS.');
    }

    public function test_sms_provider_failure_is_not_visible_to_the_requester(): void
    {
        $this->member();
        $this->gatewayFails = true;

        $this->postJson('/api/v1/auth/login/request-otp', ['phone' => self::KNOWN])
            ->assertOk()
            ->assertJsonPath('message', 'If the request is valid, a verification code has been sent.');
    }

    public function test_registration_otp_response_is_identical_for_registered_and_new_numbers(): void
    {
        $this->member();

        $registered = $this->postJson('/api/v1/auth/register/request-otp', ['phone' => self::KNOWN]);
        $fresh = $this->postJson('/api/v1/auth/register/request-otp', ['phone' => self::UNKNOWN]);

        $registered->assertOk();
        $fresh->assertOk();
        $this->assertSame($registered->getContent(), $fresh->getContent());

        $this->assertCount(1, $this->sent);
        $this->assertSame(self::UNKNOWN, $this->sent[0]['phone']);
    }

    public function test_admin_otp_request_does_not_reveal_which_numbers_are_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = $this->member();

        $forAdmin = $this->post(route('admin.login.request-otp'), ['phone' => $admin->phone]);
        $forCustomer = $this->post(route('admin.login.request-otp'), ['phone' => $customer->phone]);
        $forUnknown = $this->post(route('admin.login.request-otp'), ['phone' => self::UNKNOWN]);

        foreach ([$forAdmin, $forCustomer, $forUnknown] as $response) {
            $response->assertRedirect()->assertSessionHas('otp_sent', true);
        }

        // Immediately again for the real admin: the cooldown must not become an oracle.
        $again = $this->post(route('admin.login.request-otp'), ['phone' => $admin->phone]);
        $again->assertRedirect()->assertSessionHas('otp_sent', true)->assertSessionHasNoErrors();

        $this->assertCount(1, $this->sent);
        $this->assertSame($admin->phone, $this->sent[0]['phone']);
    }
}
