<?php

namespace Tests\Feature\Auth;

use App\Enums\Auth\UserStatus;
use App\Models\Auth\SocialAccount;
use App\Models\User;
use App\Services\Auth\SocialAccountService;
use App\Support\SignupRateLimiter;
use DomainException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;
use Tests\TestCase;

final class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['google', 'facebook'] as $provider) {
            config(['services.'.$provider => [
                'client_id' => 'test-client', 'client_secret' => 'test-secret',
                'redirect' => 'http://localhost/auth/social/'.$provider.'/callback',
            ]]);
        }
    }

    private function authenticateCustomer(User $user): void
    {
        $token = $user->createToken('web-client', ['auth', 'account']);
        $user->withAccessToken($token->accessToken);
        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken);
        Auth::forgetGuards();
    }

    private function flow(string $provider = 'google', ?User $user = null): array
    {
        return ['provider' => $provider, 'started_at' => time(), 'next' => $user ? '/account/security' : '/checkout', 'user_id' => $user?->id, 'token_id' => $user?->currentAccessToken()?->id];
    }

    private function identity(string $id = 'subject-1', ?string $email = 'customer@example.test', bool $verified = true): ProviderUser
    {
        return (new ProviderUser)->setRaw(['email_verified' => $verified])->map(['id' => $id, 'name' => 'Social Customer', 'email' => $email]);
    }

    private function mockExchange(string $provider = 'google', string $subject = 'subject-1'): MockHandler
    {
        Socialite::forgetDrivers();
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'provider-token-not-stored', 'token_type' => 'Bearer'])),
            new Response(200, [], json_encode($provider === 'google'
                ? ['sub' => $subject, 'name' => 'Social Customer', 'email' => 'customer@example.test', 'email_verified' => true]
                : ['id' => $subject, 'name' => 'Social Customer'])),
        ]);
        Socialite::extend($provider, function ($app) use ($provider, $mock) {
            $class = $provider === 'google' ? \Laravel\Socialite\Two\GoogleProvider::class : \Laravel\Socialite\Two\FacebookProvider::class;
            return (new $class($app['request'], 'test-client', 'test-secret', 'http://localhost/auth/social/'.$provider.'/callback'))->setHttpClient(new Client(['handler' => HandlerStack::create($mock)]));
        });
        return $mock;
    }

    public function test_unconfigured_providers_are_disabled_without_exposing_credentials(): void
    {
        config(['services.google.client_secret' => null, 'services.facebook.client_id' => null]);
        $this->getJson('/api/v1/auth/social/providers')->assertOk()
            ->assertExactJson(['data' => [['provider' => 'google', 'enabled' => false], ['provider' => 'facebook', 'enabled' => false]]]);
        $this->get('/auth/social/google/redirect')->assertRedirect('/login?social_error=unavailable');
        $this->get('/auth/social/instagram/redirect')->assertNotFound();
    }

    public function test_google_start_uses_state_pkce_and_an_allowlisted_return_path(): void
    {
        $response = $this->get('/auth/social/google/redirect?next=https://evil.test');
        $response->assertRedirect()->assertSessionHas('social_flow.next', '/account')->assertSessionHas('state')->assertSessionHas('code_verifier');
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertNotEmpty($query['state']);
        $this->assertNotEmpty($query['code_challenge']);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertSame('accounts.google.com', parse_url($response->headers->get('Location'), PHP_URL_HOST));
        $this->assertStringNotContainsString('test-secret', $response->headers->get('Location'));
    }

    public function test_google_callback_creates_customer_and_cookie_then_replay_is_rejected(): void
    {
        $this->mockExchange();
        $response = $this->withSession(['social_flow' => $this->flow(), 'state' => 'expected-state', 'code_verifier' => 'verifier'])
            ->get('/auth/social/google/callback?state=expected-state&code=provider-code');
        $response->assertRedirect('/checkout?social=success')->assertCookie(config('web_session.cookie'));
        $user = User::query()->sole();
        $this->assertSame('customer@example.test', $user->email);
        $this->assertNull($user->phone);
        $this->assertNull($user->phone_verified_at);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->hasRole('user'));
        $this->assertSame(['auth', 'account', 'commerce', 'cart'], $user->tokens()->sole()->abilities);
        $this->assertDatabaseCount('social_accounts', 1);
        $this->assertSame(64, strlen(SocialAccount::query()->sole()->subject_hash));
        $this->assertStringNotContainsString('provider-token', json_encode($user->toArray()));
        $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === config('web_session.cookie'));
        $this->assertTrue($cookie->isHttpOnly());
        $this->get('/auth/social/google/callback?state=expected-state&code=provider-code')->assertRedirect('/login?social_error=expired&next=%2Faccount');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_wrong_state_is_rejected_before_the_provider_is_contacted(): void
    {
        $mock = $this->mockExchange();
        $this->withSession(['social_flow' => $this->flow(), 'state' => 'real-state'])
            ->get('/auth/social/google/callback?state=forged-state&code=code')
            ->assertRedirect('/login?social_error=failed&next=%2Fcheckout');
        $this->assertSame(2, count($mock));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_expired_wrong_provider_and_cancelled_flows_create_no_user(): void
    {
        $old = $this->flow(); $old['started_at'] = time() - 601;
        $this->withSession(['social_flow' => $old])->get('/auth/social/google/callback')->assertRedirect('/login?social_error=expired&next=%2Faccount');
        $this->withSession(['social_flow' => $this->flow('facebook')])->get('/auth/social/google/callback')->assertRedirect('/login?social_error=expired&next=%2Faccount');
        $this->withSession(['social_flow' => $this->flow()])->get('/auth/social/google/callback?error=access_denied')->assertRedirect('/login?social_error=cancelled&next=%2Fcheckout');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_existing_email_requires_account_login_instead_of_automatic_linking(): void
    {
        $existing = User::factory()->create(['email' => 'customer@example.test']);
        $this->mockExchange();
        $this->withSession(['social_flow' => $this->flow(), 'state' => 'state'])
            ->get('/auth/social/google/callback?state=state&code=code')
            ->assertRedirect('/login?social_error=account_exists&next=%2Fcheckout');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertSame(0, $existing->tokens()->count());
    }

    public function test_facebook_identity_without_email_can_register_and_return_to_same_account(): void
    {
        $service = app(SocialAccountService::class);
        $request = Request::create('/auth/social/facebook/callback');
        $first = $service->resolve('facebook', $this->identity('facebook-subject', null, false), $request);
        $again = $service->resolve('facebook', $this->identity('facebook-subject', 'untrusted@example.test'), $request);
        $this->assertSame($first->id, $again->id);
        $this->assertNull($again->email);
        $this->assertNull($again->email_verified_at);
        $this->assertNull($again->phone);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_unverified_google_email_is_not_used_as_recovery_email(): void
    {
        $user = app(SocialAccountService::class)->resolve('google', $this->identity('new-id', 'unverified@example.test', false), Request::create('/'));
        $this->assertNull($user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_signed_in_customer_can_link_and_sign_in_again_without_a_second_account(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.test']);
        $this->authenticateCustomer($user);
        $this->mockExchange();
        $this->withSession(['social_flow' => $this->flow('google', $user), 'state' => 'state'])
            ->get('/auth/social/google/callback?state=state&code=code')->assertRedirect('/account/security?social=linked');
        $this->getJson('/api/v1/auth/social/accounts')->assertOk()->assertExactJson(['data' => [['provider' => 'google']]]);
        $resolved = app(SocialAccountService::class)->resolve('google', $this->identity(), Request::create('/'));
        $this->assertSame($user->id, $resolved->id);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_link_start_requires_same_origin_and_an_existing_session(): void
    {
        $this->withHeader('Sec-Fetch-Site', 'same-origin')->get('/auth/social/google/redirect?intent=link')->assertForbidden();
        $user = User::factory()->create();
        $this->authenticateCustomer($user);
        $this->withHeader('Sec-Fetch-Site', 'cross-site')->get('/auth/social/google/redirect?intent=link')->assertForbidden();
        $this->withHeader('Sec-Fetch-Site', 'same-origin')->get('/auth/social/google/redirect?intent=link')->assertRedirect()->assertSessionHas('social_flow.user_id', $user->id);
    }

    public function test_link_callback_refuses_a_changed_session(): void
    {
        $original = User::factory()->create();
        $other = User::factory()->create();
        $this->authenticateCustomer($other);
        $this->mockExchange();
        $this->withSession(['social_flow' => $this->flow('google', $original), 'state' => 'state'])
            ->get('/auth/social/google/callback?state=state&code=code')
            ->assertRedirect('/account/security?social_error=session_changed');
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_social_identity_cannot_be_linked_to_another_customer(): void
    {
        $service = app(SocialAccountService::class);
        $service->resolve('google', $this->identity(), Request::create('/'));
        $other = User::factory()->create();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('identity_in_use');
        $service->resolve('google', $this->identity(), Request::create('/'), $other);
    }

    public function test_inactive_social_customer_cannot_return(): void
    {
        $service = app(SocialAccountService::class);
        $user = $service->resolve('google', $this->identity(), Request::create('/'));
        $user->forceFill(['status' => 'blocked'])->save();
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('account_unavailable');
        $service->resolve('google', $this->identity(), Request::create('/'));
    }

    public function test_social_registration_shares_password_signup_limits(): void
    {
        config(['security.signup_ip_per_minute' => 1]);
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        app(SignupRateLimiter::class)->hit($request);
        $this->expectException(ValidationException::class);
        app(SocialAccountService::class)->resolve('google', $this->identity(), $request);
    }
}
