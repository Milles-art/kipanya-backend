<?php

use App\Http\Middleware\AddRequestId;
use App\Http\Middleware\EnsureAdminPermission;
use App\Http\Middleware\EnsureAdminWebAccess;
use App\Http\Middleware\EnsureScopedApiToken;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureWebSessionCookie;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Honour X-Forwarded-* headers from the deployment's reverse proxy so
        // scheme/client-IP detection is correct behind TLS termination.
        $trustedProxies = env('TRUSTED_PROXIES');
        if (is_string($trustedProxies) && $trustedProxies !== '') {
            $middleware->trustProxies(
                at: $trustedProxies === '*'
                    ? '*'
                    : array_map('trim', explode(',', $trustedProxies)),
            );
        }

        $middleware->append(SecurityHeaders::class);
        // The cookie->bearer shim must run before any auth guard on every
        // request path (the api group is not reliably ordered before route
        // middleware in the test client).
        $middleware->append(EnsureWebSessionCookie::class);
        // The web-session cookie is an opaque bearer token validated server
        // side, so there is nothing to gain from Laravel's cookie encryption.
        $middleware->encryptCookies(except: [
            env('KP_WEB_SESSION_COOKIE', 'kp_web_session'),
        ]);
        $middleware->appendToGroup('api', [AddRequestId::class]);
        $middleware->alias([
            'permission' => EnsureUserHasPermission::class,
            'admin.permission' => EnsureAdminPermission::class,
            'admin.web' => EnsureAdminWebAccess::class,
            'active' => EnsureUserIsActive::class,
            'scoped.token' => EnsureScopedApiToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
