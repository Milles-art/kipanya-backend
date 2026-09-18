<?php

namespace App\Providers;

use App\Integrations\Payments\FakePaymentGateway;
use App\Integrations\Payments\PaymentGateway;
use App\Integrations\Payments\SelcomCheckoutGateway;
use App\Integrations\Sms\LogSmsGateway;
use App\Integrations\Sms\NotifyAfricaSmsGateway;
use App\Integrations\Sms\SmsGateway;
use App\Support\ProductionSecurityGuard;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function (Application $app): PaymentGateway {
            $config = $app['config']->get('services.selcom', []);
            $apiKey = $config['api_key'] ?? null;
            $apiSecret = $config['api_secret'] ?? null;
            $vendorId = $config['vendor_id'] ?? null;
            $baseUrl = $config['base_url'] ?? null;

            $configured = fn ($value): bool => is_string($value) && trim($value) !== '';

            // Fully configured server-side credentials enable the real gateway;
            // otherwise the fake gateway keeps local development/testing
            // self-contained. Production refuses silent fallback because
            // FakePaymentGateway throws in production environments.
            if ($configured($apiKey) && $configured($apiSecret) && $configured($vendorId) && $configured($baseUrl)) {
                return new SelcomCheckoutGateway(
                    baseUrl: (string) $baseUrl,
                    apiKey: (string) $apiKey,
                    apiSecret: (string) $apiSecret,
                    vendorId: (string) $vendorId,
                    currency: (string) ($config['currency'] ?? 'TZS'),
                    redirectUrl: (string) ($config['redirect_url'] ?? ''),
                    cancelUrl: (string) ($config['cancel_url'] ?? ''),
                    webhookUrl: ($config['webhook_url'] ?? null) ?: null,
                    timeout: (int) ($config['timeout'] ?? 10),
                );
            }

            return new FakePaymentGateway;
        });

        $this->app->singleton(SmsGateway::class, function (Application $app): SmsGateway {
            $config = $app['config']->get('services.notify_africa', []);
            $apiKey = $config['api_key'] ?? null;

            // A configured server-side key enables real delivery; otherwise the
            // log gateway keeps local development self-contained.
            if (is_string($apiKey) && trim($apiKey) !== '') {
                return new NotifyAfricaSmsGateway(
                    baseUrl: (string) ($config['base_url'] ?? 'https://api.notify.africa'),
                    apiKey: $apiKey,
                    senderId: (string) ($config['sender_id'] ?? ''),
                    timeout: (int) ($config['timeout'] ?? 10),
                );
            }

            return new LogSmsGateway;
        });
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
