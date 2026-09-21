<?php

namespace App\Providers;

use App\Integrations\Payments\FakePaymentGateway;
use App\Integrations\Payments\PaymentGateway;
use App\Integrations\Sms\LogSmsGateway;
use App\Integrations\Sms\SmsGateway;
use App\Support\ProductionSecurityGuard;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
        $this->app->bind(SmsGateway::class, LogSmsGateway::class);
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => [
            Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()),
        ]);

        // Behind a TLS-terminating proxy the application may see plain HTTP,
        // so generated URLs (redirects, links, cookie Secure checks) are forced
        // to HTTPS whenever the deployment opts in.
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        // Refuse to boot in production with an insecure configuration
        // (debug output, unencrypted sessions, non-secure cookies, wildcard
        // CORS, or a config cache baked from a local environment).
        ProductionSecurityGuard::assert();
    }
}
