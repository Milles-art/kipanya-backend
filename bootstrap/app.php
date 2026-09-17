<?php

use App\Http\Middleware\AddRequestId;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureAdminPermission;
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
        $middleware->append(SecurityHeaders::class);
        $middleware->appendToGroup('api', [AddRequestId::class]);
        $middleware->alias([
            'permission' => EnsureUserHasPermission::class,
            'admin.permission' => EnsureAdminPermission::class,
            'admin.web' => \App\Http\Middleware\EnsureAdminWebAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
