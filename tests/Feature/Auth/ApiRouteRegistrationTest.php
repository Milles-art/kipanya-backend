<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRouteRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_api_routes_are_registered(): void
    {
        $this->postJson('/api/v1/auth/register/request-otp', [])
            ->assertStatus(422);

        $this->postJson('/api/v1/auth/login/request-otp', [])
            ->assertStatus(422);

        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }
}
