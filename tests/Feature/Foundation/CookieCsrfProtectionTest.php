<?php

namespace Tests\Feature\Foundation;

use App\Actions\Auth\IssueSanctumToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * F-07: a request authenticated only by the browser cookie may not change state
 * unless it is provably same-origin.
 */
final class CookieCsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['phone' => '+255712345678', 'phone_verified_at' => now(), 'status' => 'active']);
        $this->token = app(IssueSanctumToken::class)->execute($user);
    }

    /** POST /auth/logout is a cheap authenticated, state-changing endpoint. */
    private function cookieRequest(array $headers = [])
    {
        return $this->withUnencryptedCookies(['kp_web_session' => $this->token])
            ->withHeaders(array_merge(['Accept' => 'application/json'], $headers))
            ->post('/api/v1/auth/logout');
    }

    public function test_cookie_authenticated_post_without_provenance_headers_is_blocked(): void
    {
        $this->cookieRequest()->assertStatus(403)->assertJsonPath('message', 'Cross-site request blocked.');
    }

    public function test_cookie_authenticated_post_from_a_foreign_origin_is_blocked(): void
    {
        $this->cookieRequest(['Origin' => 'https://evil.example'])->assertStatus(403);
    }

    public function test_cookie_authenticated_post_from_a_lookalike_origin_is_blocked(): void
    {
        $this->cookieRequest(['Origin' => config('app.url').'.evil.example'])->assertStatus(403);
        $this->cookieRequest(['Origin' => 'null'])->assertStatus(403);
    }

    public function test_cross_site_and_same_site_fetch_metadata_is_blocked(): void
    {
        $this->cookieRequest(['Sec-Fetch-Site' => 'cross-site'])->assertStatus(403);
        $this->cookieRequest(['Sec-Fetch-Site' => 'same-site'])->assertStatus(403);
        $this->cookieRequest(['Sec-Fetch-Site' => 'none'])->assertStatus(403);
    }

    public function test_same_origin_requests_are_allowed(): void
    {
        $this->cookieRequest(['Sec-Fetch-Site' => 'same-origin'])->assertOk();
    }

    public function test_matching_origin_or_referer_is_allowed_when_fetch_metadata_is_absent(): void
    {
        $this->cookieRequest(['Origin' => config('app.url')])->assertOk();
    }

    public function test_matching_referer_is_allowed_when_origin_is_absent(): void
    {
        $this->cookieRequest(['Referer' => config('app.url').'/account'])->assertOk();
    }

    public function test_safe_methods_with_the_cookie_are_not_restricted(): void
    {
        $this->withUnencryptedCookies(['kp_web_session' => $this->token])
            ->withHeader('Accept', 'application/json')
            ->get('/api/v1/auth/me')
            ->assertOk();
    }

    public function test_bearer_token_clients_are_unaffected(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer '.$this->token, 'Accept' => 'application/json'])
            ->post('/api/v1/auth/logout')
            ->assertOk();
    }
}
