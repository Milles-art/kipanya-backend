<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Tests\TestCase;

/**
 * Password sign-in lockout. Successor to the OTP lockout regression suite:
 * failed attempts are counted OUTSIDE any database transaction, so neither a
 * thrown response nor a database-cache store can erase the hit (F-01).
 */
class LoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '+255712345678';

    /** Attempts allowed per phone+IP before the lockout window kicks in. */
    private const MAX_ATTEMPTS = 5;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'phone' => self::PHONE,
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => 'correct-horse',
        ]);
    }

    private function attempt(string $password): void
    {
        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'password' => $password])
            ->assertStatus(422);
    }

    public function test_repeated_wrong_passwords_lock_out_even_the_correct_one(): void
    {
        // Isolate the controller lockout from the per-IP route throttle.
        $this->withoutMiddleware(ThrottleRequests::class);

        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $this->attempt('wrong-password');
        }

        // Even the correct password must now be refused.
        $this->attempt('correct-horse');
        $this->assertGuest('sanctum');
        $this->assertDatabaseCount('personal_access_tokens', 0);
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

        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $this->attempt('wrong-password');
        }

        $this->attempt('correct-horse');
    }

    public function test_a_successful_login_clears_the_attempt_counter(): void
    {
        $this->attempt('wrong-password');

        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'password' => 'correct-horse'])
            ->assertOk()
            ->assertJsonPath('user.phone', self::PHONE);

        // The budget restarted: one more wrong attempt does not lock out.
        $this->attempt('wrong-password');

        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'password' => 'correct-horse'])
            ->assertOk();
    }

    public function test_the_lockout_is_scoped_to_one_phone_and_ip_pair(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);

        $other = User::factory()->create([
            'phone' => '+255712345999',
            'phone_verified_at' => now(),
            'status' => 'active',
            'password' => 'other-horse',
        ]);

        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            $this->attempt('wrong-password');
        }

        // Locking one account must not lock the whole shop.
        $this->postJson('/api/v1/auth/login', ['phone' => $other->phone, 'password' => 'other-horse'])
            ->assertOk();
    }

    public function test_an_inactive_user_is_refused_even_with_the_correct_password(): void
    {
        User::query()->wherePhone(self::PHONE)->update(['status' => 'inactive']);

        $this->postJson('/api/v1/auth/login', ['phone' => self::PHONE, 'password' => 'correct-horse'])
            ->assertStatus(422);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
