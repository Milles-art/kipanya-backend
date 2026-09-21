<?php

namespace Tests\Unit\Support;

use App\Support\ProductionSecurityGuard;
use RuntimeException;
use Tests\TestCase;

class ProductionSecurityGuardTest extends TestCase
{
    public function test_production_guard_rejects_insecure_configuration(): void
    {
        config()->set('app.env', 'production');
        config()->set('app.key', null);
        config()->set('app.debug', true);
        config()->set('app.force_https', false);
        config()->set('app.url', 'http://insecure.example');
        config()->set('session.secure', false);
        config()->set('session.encrypt', false);
        config()->set('cors.allowed_origins', ['http://localhost:5173']);
        config()->set('auth.expose_otp_codes', true);
        config()->set('auth.log_otp_codes', false);

        try {
            ProductionSecurityGuard::assert();
            $this->fail('Expected a RuntimeException for insecure production config.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('APP_DEBUG', $e->getMessage());
            $this->assertStringContainsString('APP_FORCE_HTTPS', $e->getMessage());
            $this->assertStringContainsString('SESSION_SECURE_COOKIE', $e->getMessage());
            $this->assertStringContainsString('SESSION_ENCRYPT', $e->getMessage());
            $this->assertStringContainsString('CORS_ALLOWED_ORIGINS', $e->getMessage());
            $this->assertStringContainsString('OTP', $e->getMessage());
            $this->assertStringContainsString('TRUSTED_PROXIES', $e->getMessage());
            $this->assertStringContainsString('NOTIFY_AFRICA_API_KEY', $e->getMessage());
        }
    }

    public function test_production_guard_allows_secure_configuration(): void
    {
        config()->set('app.env', 'production');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('app.debug', false);
        config()->set('app.force_https', true);
        config()->set('app.url', 'https://kipanya.example');
        config()->set('session.secure', true);
        config()->set('session.encrypt', true);
        config()->set('cors.allowed_origins', ['https://app.kipanya.example']);
        config()->set('auth.expose_otp_codes', false);
        config()->set('auth.log_otp_codes', false);
        config()->set('app.trusted_proxies', '*');
        config()->set('services.notify_africa.api_key', 'ntfy_prod_test_key');
        config()->set('security.admin_require_2fa', true);
        config()->set('security.money_actions_require_totp', true);
        config()->set('logging.default', 'single');
        config()->set('logging.channels.single.level', 'warning');
        config()->set('services.selcom.base_url', 'https://apigw.selcommobile.com');
        config()->set('services.selcom.api_key', 'prod-key');
        config()->set('services.selcom.api_secret', 'prod-secret');
        config()->set('services.selcom.vendor_id', 'VENDOR1');
        config()->set('services.selcom.webhook_url', 'https://kipanya.example/api/webhooks/selcom');

        $this->expectNotToPerformAssertions();
        ProductionSecurityGuard::assert();
    }

    public function test_guard_is_noop_outside_production(): void
    {
        config()->set('app.env', 'local');
        config()->set('app.debug', true);
        config()->set('session.secure', false);

        $this->expectNotToPerformAssertions();
        ProductionSecurityGuard::assert();
    }

    public function test_production_guard_requires_payment_gateway_settings_and_admin_two_factor(): void
    {
        config()->set('app.env', 'production');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('app.debug', false);
        config()->set('app.force_https', true);
        config()->set('app.url', 'https://kipanya.example');
        config()->set('session.secure', true);
        config()->set('session.encrypt', true);
        config()->set('cors.allowed_origins', ['https://app.kipanya.example']);
        config()->set('auth.expose_otp_codes', false);
        config()->set('auth.log_otp_codes', false);
        config()->set('app.trusted_proxies', '10.0.0.1');
        config()->set('services.notify_africa.api_key', 'ntfy_prod_test_key');
        config()->set('security.admin_require_2fa', false);
        config()->set('services.selcom.base_url', 'http://insecure.selcom.example');
        config()->set('services.selcom.api_key', '');
        config()->set('services.selcom.api_secret', null);
        config()->set('services.selcom.vendor_id', '');
        config()->set('services.selcom.webhook_url', '');

        try {
            ProductionSecurityGuard::assert();
            $this->fail('Expected a RuntimeException.');
        } catch (RuntimeException $e) {
            foreach (['ADMIN_REQUIRE_2FA', 'SELCOM_API_KEY', 'SELCOM_API_SECRET', 'SELCOM_VENDOR_ID', 'SELCOM_WEBHOOK_URL', 'SELCOM_BASE_URL must use https'] as $needle) {
                $this->assertStringContainsString($needle, $e->getMessage());
            }
        }
    }

    public function test_production_guard_refuses_debug_level_logging(): void
    {
        config()->set('app.env', 'production');
        config()->set('logging.default', 'single');
        config()->set('logging.channels.single.level', 'debug');

        try {
            ProductionSecurityGuard::assert();
            $this->fail('Expected a RuntimeException.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('LOG_LEVEL must not be "debug"', $e->getMessage());
        }

        config()->set('logging.channels.single.level', 'warning');
        try {
            ProductionSecurityGuard::assert();
        } catch (RuntimeException $e) {
            $this->assertStringNotContainsString('LOG_LEVEL', $e->getMessage());
        }
    }
}
