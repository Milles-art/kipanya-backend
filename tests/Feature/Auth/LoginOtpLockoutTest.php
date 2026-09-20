<?php

namespace Tests\Feature\Auth;

use App\Integrations\Sms\SmsGateway;
use App\Models\Auth\OtpCode;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Tests\TestCase;

/**
 * Regression tests for F-01: the customer login endpoint used to run
 * OtpService::verify() inside a DB transaction. A wrong code throws, the
 * transaction rolled back, and both the per-code attempt counter and (with the
 * database cache store) the lockout counter were undone.
 */
class LoginOtpLockoutTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '+255712345678';

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

        User::factory()->create([
            'phone' => self::PHONE,
            'phone_verified_at' => now(),
            'status' => 'active',
        ]);
    }

    private function requestCode(): string
    {
        $this->postJson('/api/v1/auth/login/request-otp', ['phone' => self::PHONE])->assertOk();
        preg_match('/\b\d{6}\b/', end($this->sent)['message'], $m);

        return $m[0];
    }

    private function wrongCode(string $real): string
    {
        return $real === '000000' ? '111111' : '000000';
    }

    public function test_a_wrong_code_on_login_increments_the_per_code_attempt_counter(): void
    {
        $code = $this->requestCode();

        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'code' => $this->wrongCode($code)])
            ->assertStatus(422);

        $this->assertSame(
            1,
            (int) OtpCode::query()->where('phone', self::PHONE)->latest('id')->value('attempts'),
            'The failed attempt must persist; it was rolled back with the login transaction.',
        );
    }

    public function test_login_is_locked_out_after_repeated_wrong_codes(): void
    {
        // Isolate the OTP lockout from the per-IP route throttle (which would 429 first).
        $this->withoutMiddleware(ThrottleRequests::class);

        $code = $this->requestCode();
        $wrong = $this->wrongCode($code);

        for ($i = 0; $i < OtpService::MAX_VERIFY_FAILURES; $i++) {
            $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'code' => $wrong])->assertStatus(422);
        }

        // Even the correct code must now be refused.
        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'code' => $code])->assertStatus(422);
        $this->assertGuest('sanctum');
    }

    public function test_lockout_still_works_when_the_rate_limiter_uses_the_database_cache_store(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);

        // Production default in .env.example: CACHE_STORE=database on the same
        // connection as the application data. A rollback used to erase the hit.
        config(['cache.default' => 'database']);
        $this->app->forgetInstance('cache');
        $this->app->forgetInstance('cache.store');
        $this->app->forgetInstance(\Illuminate\Cache\RateLimiter::class);
        Facade::clearResolvedInstance('cache');
        Facade::clearResolvedInstance(\Illuminate\Cache\RateLimiter::class);
        $this->assertSame('database', Cache::getDefaultDriver());

        $code = $this->requestCode();
        $wrong = $this->wrongCode($code);

        for ($i = 0; $i < OtpService::MAX_VERIFY_FAILURES; $i++) {
            $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'code' => $wrong]);
        }

        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'code' => $code])->assertStatus(422);
    }

    public function test_the_correct_code_still_logs_in(): void
    {
        $code = $this->requestCode();

        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'code' => $code])
            ->assertOk()
            ->assertJsonPath('user.phone', self::PHONE);
    }

    public function test_an_inactive_user_is_refused_even_with_a_valid_code(): void
    {
        $code = $this->requestCode();
        User::query()->where('phone', self::PHONE)->update(['status' => 'inactive']);

        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'code' => $code])->assertStatus(422);
    }
}
