<?php

namespace Tests\Feature\Foundation;

use Tests\TestCase;

/**
 * Regression coverage: OTP codes must only ever be logged or exposed in
 * local/testing, even when the environment variables are mistakenly enabled
 * (e.g. a local .env leaked into production).
 */
class OtpDebugConfigTest extends TestCase
{
    public function test_otp_debug_flags_follow_the_environment(): void
    {
        $this->withEnvironment([
            'APP_ENV' => 'production',
            'AUTH_LOG_OTP_CODES' => 'true',
            'AUTH_EXPOSE_OTP_CODES' => 'true',
        ], function (): void {
            $config = require base_path('config/auth.php');

            $this->assertFalse($config['log_otp_codes']);
            $this->assertFalse($config['expose_otp_codes']);
        });

        $this->withEnvironment(['APP_ENV' => 'local'], function (): void {
            $config = require base_path('config/auth.php');

            $this->assertTrue($config['log_otp_codes']);
            $this->assertTrue($config['expose_otp_codes']);
        });
    }

    private function withEnvironment(array $values, callable $callback): void
    {
        $originalEnv = [];
        $originalServer = [];

        foreach ($values as $key => $value) {
            $originalEnv[$key] = $_ENV[$key] ?? null;
            $originalServer[$key] = $_SERVER[$key] ?? null;

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }

        try {
            $callback();
        } finally {
            foreach ($values as $key => $value) {
                if ($originalEnv[$key] === null) {
                    unset($_ENV[$key], $_SERVER[$key]);
                    putenv($key);

                    continue;
                }

                $_ENV[$key] = $originalEnv[$key];
                $_SERVER[$key] = $originalServer[$key] ?? $originalEnv[$key];
                putenv("{$key}={$originalEnv[$key]}");
            }
        }
    }
}
