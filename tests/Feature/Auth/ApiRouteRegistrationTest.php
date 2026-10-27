<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRouteRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_api_routes_are_registered(): void
    {
        // Password-based auth endpoints: empty payloads fail validation (422),
        // proving the routes exist and run their FormRequests.
        $this->postJson('/api/v1/auth/register', [])
            ->assertStatus(422);

        $this->postJson('/api/v1/auth/login', [])
            ->assertStatus(422);

        $this->postJson('/api/v1/auth/forgot-password', [])
            ->assertStatus(422);

        $this->postJson('/api/v1/auth/reset-password', [])
            ->assertStatus(422);

        $this->postJson('/api/v1/auth/checkout/signin', [])
            ->assertStatus(422);

        // The SMS-code endpoints are gone for good.
        $this->postJson('/api/v1/auth/register/request-otp', [])
            ->assertNotFound();
        $this->postJson('/api/v1/auth/login/request-otp', [])
            ->assertNotFound();
        $this->postJson('/api/v1/auth/checkout/request-code', [])
            ->assertNotFound();
        $this->postJson('/api/v1/auth/checkout/verify', [])
            ->assertNotFound();

        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }
}
