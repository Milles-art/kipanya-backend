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

    public function test_register_rejects_malformed_phone(): void
    {
        foreach (self::INVALID_PHONES as $phone) {
            $this->postJson('/api/v1/auth/register', [
                'phone' => $phone,
                'name' => 'Test User',
                'password' => 'secret-pass-123',
                'password_confirmation' => 'secret-pass-123',
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
                'password' => 'secret-pass-123',
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors('phone');
        }
    }

    public function test_forgot_password_rejects_malformed_phone(): void
    {
        foreach (self::INVALID_PHONES as $phone) {
            $this->postJson('/api/v1/auth/forgot-password', ['phone' => $phone])
                ->assertStatus(422)
                ->assertJsonValidationErrors('phone');
        }
    }

    public function test_checkout_signin_rejects_malformed_phone(): void
    {
        foreach (self::INVALID_PHONES as $phone) {
            $this->postJson('/api/v1/auth/checkout/signin', [
                'phone' => $phone,
                'name' => 'Guest Buyer',
                'password' => 'secret-pass-123',
            ])
                ->assertStatus(422)
                ->assertJsonValidationErrors('phone');
        }
    }
}
