<?php

use App\Http\Middleware\AddRequestId;
use App\Http\Middleware\EnsureAdminPermission;
use App\Http\Middleware\EnsureAdminTwoFactor;
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
        // Proxy trust and the cookie-encryption exception are applied from CONFIG in
        // App\Support\ProxyTrust (called by AppServiceProvider). Never read env() here:
        // it returns null once config is cached.
        $middleware->append(SecurityHeaders::class);
        // The cookie->bearer shim must run before any auth guard on every
        // request path (the api group is not reliably ordered before route
        // middleware in the test client).
        $middleware->append(EnsureWebSessionCookie::class);
        $middleware->appendToGroup('api', [AddRequestId::class]);
        $middleware->alias([
            'permission' => EnsureUserHasPermission::class,
            'admin.permission' => EnsureAdminPermission::class,
            'admin.web' => EnsureAdminWebAccess::class,
            'admin.2fa' => EnsureAdminTwoFactor::class,
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
