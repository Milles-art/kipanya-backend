<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Device-fingerprint plumbing: the kp_device cookie is issued server-side,
 * its HMAC lands on user rows at account creation (never the raw value), and
 * the creating connection's IP is recorded for the admin duplicates report.
 */
class DeviceFingerprintTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_issue_a_http_only_kp_device_cookie_when_missing(): void
    {
        $response = $this->getJson('/api/v1/wear/storefront');

        $cookieHeader = $response->headers->get('set-cookie');
        $this->assertNotNull($cookieHeader);
        $this->assertStringContainsString('kp_device=', $cookieHeader);
        $this->assertMatchesRegularExpression('/httponly/i', $cookieHeader);
    }

    public function test_an_existing_device_cookie_is_left_untouched(): void
    {
        // JSON helper requests never forward cookies, so send the cookie on a
        // plain request (Accept header keeps the JSON response).
        $response = $this->withUnencryptedCookies(['kp_device' => 'stable-device-id'])
            ->withHeader('Accept', 'application/json')
            ->get('/api/v1/wear/storefront');

        $this->assertStringNotContainsString('kp_device=', (string) $response->headers->get('set-cookie'));
    }

    public function test_signup_stores_the_device_hmac_and_the_signup_ip(): void
    {
        $this->withUnencryptedCookies(['kp_device' => 'device-abc'])
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/auth/register', [
                'phone' => '0712345678',
                'name' => 'Tracked Buyer',
                'password' => 'secret-pass-123',
                'password_confirmation' => 'secret-pass-123',
            ])->assertCreated();

        $user = User::query()->wherePhone('+255712345678')->firstOrFail();

        $this->assertSame(
            hash_hmac('sha256', 'device-abc', (string) config('app.key')),
            $user->device_hash,
            'The row must hold the HMAC of the cookie, never the raw value.',
        );
        $this->assertNotSame('device-abc', $user->device_hash);
        $this->assertSame('127.0.0.1', $user->signup_ip);
    }

    public function test_two_signups_on_one_browser_share_a_device_hash(): void
    {
        // JSON helper requests never forward cookies: use plain requests with
        // an Accept header so the kp_device cookie actually travels.
        $this->withUnencryptedCookies(['kp_device' => 'device-abc'])
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/auth/register', [
                'phone' => '0712345671',
                'name' => 'First Account',
                'password' => 'secret-pass-123',
                'password_confirmation' => 'secret-pass-123',
            ])->assertCreated();

        $this->withUnencryptedCookies(['kp_device' => 'device-abc'])
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/auth/register', [
                'phone' => '0712345672',
                'name' => 'Second Account',
                'password' => 'secret-pass-123',
                'password_confirmation' => 'secret-pass-123',
            ])->assertCreated();

        $hashes = User::query()->pluck('device_hash')->all();
        $this->assertCount(2, $hashes);
        $this->assertSame($hashes[0], $hashes[1]);
    }

    public function test_signup_without_a_device_cookie_records_no_device_hash(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'phone' => '0712345678',
            'name' => 'Cookieless Buyer',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertCreated();

        $user = User::query()->wherePhone('+255712345678')->firstOrFail();

        // First-ever request from a browser: no cookie existed yet, so the
        // anti-farm signal starts with the SECOND account from that browser.
        $this->assertNull($user->device_hash);
    }
}
