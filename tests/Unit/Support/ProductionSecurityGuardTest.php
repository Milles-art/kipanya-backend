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
}
