<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the malformed-phone 500: every auth entry point must
 * reject an unparseable phone number with a 422 validation error rather than
 * letting PhoneNumber::normalize() throw an InvalidArgumentException.
 */
class PhoneValidationRegressionTest extends TestCase
{
    use RefreshDatabase;

    private const INVALID_PHONES = [
        'abc',
        '+255',
        '0123',
        '25512345678',
        '+2551234567890',
        '...',
        '',
    ];

    public function test_registration_otp_request_rejects_malformed_phone(): void
    {
        foreach (self::INVALID_PHONES as $phone) {
            $this->postJson('/api/v1/auth/register/request-otp', ['phone' => $phone])
                ->assertStatus(422)
                ->assertJsonValidationErrors('phone');
        }
    }

    public function test_login_otp_request_rejects_malformed_phone(): void
    {
        foreach (self::INVALID_PHONES as $phone) {
            $this->postJson('/api/v1/auth/login/request-otp', ['phone' => $phone])
                ->assertStatus(422)
                ->assertJsonValidationErrors('phone');
        }
    }

    public function test_registration_rejects_malformed_phone(): void
    {
        foreach (self::INVALID_PHONES as $phone) {
            $this->postJson('/api/v1/auth/register', [
                'phone' => $phone,
                'code' => '123456',
                'name' => 'Test User',
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors('phone');
        }
    }

    public function test_login_rejects_malformed_phone(): void
    {
        foreach (self::INVALID_PHONES as $phone) {
            $this->postJson('/api/v1/auth/login', [
                'phone' => $phone,
                'code' => '123456',
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors('phone');
        }
    }
}
