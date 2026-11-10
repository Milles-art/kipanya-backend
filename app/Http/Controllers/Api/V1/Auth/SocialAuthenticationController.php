<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\IssueSanctumToken;
use App\Http\Controllers\Controller;
use App\Models\Auth\SocialAccount;
use App\Services\Auth\SocialAccountService;
use App\Support\AuditLogger;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

final class SocialAuthenticationController extends Controller
{
    private const PROVIDERS = ['google', 'facebook'];

    public function providers(): JsonResponse
    {
        return response()->json(['data' => collect(self::PROVIDERS)->map(fn ($provider) => ['provider' => $provider, 'enabled' => $this->enabled($provider)])->values()])->header('Cache-Control', 'no-store');
    }

    public function accounts(Request $request): JsonResponse
    {
        return response()->json(['data' => SocialAccount::query()->where('user_id', $request->user()->id)->pluck('provider')->map(fn ($provider) => ['provider' => $provider])->values()]);
    }

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        if (! $this->enabled($provider)) {
            return redirect('/login?social_error=unavailable');
        }
        $link = $request->query('intent') === 'link';
        $user = $request->user('sanctum');
        if ($link) {
            // A link must start from this storefront and remain bound to the
            // same authenticated browser session across the provider round trip.
            $site = $request->header('Sec-Fetch-Site');
            $referer = $request->header('Referer', '');
            $sameOrigin = $site !== null ? $site === 'same-origin' : str_starts_with($referer, $request->getSchemeAndHttpHost().'/');
            abort_unless($sameOrigin && $user?->isActive() && ! $user->isAdmin() && $user->tokenCan('auth'), 403);
        } elseif ($user) {
            return redirect('/account');
        }
        $request->session()->regenerate();
        $request->session()->put('social_flow', [
            'provider' => $provider, 'started_at' => time(),
            'next' => $link ? '/account/security' : $this->safeNext($request->query('next')),
            'user_id' => $link ? $user->id : null,
            'token_id' => $link ? $user->currentAccessToken()?->id : null,
        ]);
        $driver = $this->driver($provider);
        return $driver->redirect()->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function callback(Request $request, string $provider, SocialAccountService $accounts, IssueSanctumToken $tokens, AuditLogger $audit): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
        $flow = $request->session()->pull('social_flow');
        if (! $this->enabled($provider) || ! is_array($flow) || $flow['provider'] !== $provider || time() - $flow['started_at'] > 600) {
            $request->session()->forget('state');
            return $this->failure('expired');
        }
        if ($request->has('error')) {
            $request->session()->forget('state');
            return $this->failure('cancelled', $flow);
        }
        try {
            // Stateful Socialite verifies AND consumes the session's OAuth state.
            $identity = $this->driver($provider)->user();
            $linkTo = null;
            if ($flow['user_id'] !== null) {
                $linkTo = $request->user('sanctum');
                if (! $linkTo || $linkTo->id !== $flow['user_id'] || $linkTo->currentAccessToken()?->id !== $flow['token_id']) {
                    throw new DomainException('session_changed');
                }
            } elseif ($request->user('sanctum')) {
                throw new DomainException('session_changed');
            }
            $user = $accounts->resolve($provider, $identity, $request, $linkTo);
            $request->session()->regenerate();
            $audit->log($request, $linkTo ? 'auth.social_linked' : 'auth.social_signed_in', $user, ['provider' => $provider], $user);
            $response = redirect($flow['next'].'?social='.($linkTo ? 'linked' : 'success'));
            if (! $linkTo) {
                $response->withCookie(Cookie::make(
                    config('web_session.cookie'), $tokens->execute($user), (int) (config('sanctum.expiration') ?? 43200),
                    config('web_session.path'), config('web_session.domain'), config('web_session.secure'), true, false, config('web_session.same_site'),
                ));
            }
            return $response->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
        } catch (DomainException $exception) {
            return $this->failure($exception->getMessage(), $flow);
        } catch (Throwable $exception) {
            // Provider error bodies may contain codes, tokens or customer data.
            Log::warning('Social sign-in failed.', ['provider' => $provider, 'exception' => $exception::class]);
            return $this->failure('failed', $flow);
        }
    }

    private function enabled(string $provider): bool
    {
        return collect(['client_id', 'client_secret', 'redirect'])->every(fn ($key) => filled(config('services.'.$provider.'.'.$key)));
    }

    private function driver(string $provider): \Laravel\Socialite\Two\AbstractProvider
    {
        $driver = Socialite::driver($provider);
        if ($provider === 'google') {
            return $driver->setScopes(['openid', 'profile', 'email'])->enablePKCE();
        }
        return $driver->setScopes(['public_profile'])->fields(['id', 'name']);
    }

    private function safeNext(mixed $next): string
    {
        return is_string($next) && preg_match('~^/(?:checkout|account(?:/[a-zA-Z0-9_-]+)*)$~', $next) ? $next : '/account';
    }

    private function failure(string $code, ?array $flow = null): RedirectResponse
    {
        $target = ($flow['user_id'] ?? null) !== null ? '/account/security' : '/login';
        $next = ($flow['user_id'] ?? null) === null ? '&next='.rawurlencode($flow['next'] ?? '/account') : '';
        return redirect($target.'?social_error='.rawurlencode($code).$next)->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }
}
