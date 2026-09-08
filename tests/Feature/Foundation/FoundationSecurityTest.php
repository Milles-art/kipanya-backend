<?php

namespace Tests\Feature\Foundation;

use App\Enums\Auth\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorization_foundation_tables_and_default_roles_exist(): void
    {
        $this->assertDatabaseHas('roles', ['slug' => 'user']);
        $this->assertDatabaseHas('roles', ['slug' => 'super_admin']);
        $this->assertDatabaseHas('permissions', ['slug' => 'content.cartoons.manage']);
    }

    public function test_legacy_admin_maps_to_super_admin_permission_compatibility(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->hasPermission('content.cartoons.manage'));
    }

    public function test_regular_user_does_not_get_admin_permissions(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->assertFalse($user->hasPermission('content.cartoons.manage'));
    }

    public function test_api_responses_include_a_request_id(): void
    {
        $response = $this->getJson('/api/v1/content/cartoons');

        $response->assertOk();
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }
}
