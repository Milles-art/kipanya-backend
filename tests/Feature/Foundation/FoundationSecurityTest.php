<?php

namespace Tests\Feature\Foundation;

use App\Enums\Auth\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorization_foundation_tables_and_kp_wear_permissions_exist(): void
    {
        $this->assertDatabaseHas('roles', ['slug' => 'user']);
        $this->assertDatabaseHas('roles', ['slug' => 'super_admin']);
        $this->assertDatabaseHas('roles', ['slug' => 'commerce_manager']);
        $this->assertDatabaseHas('permissions', ['slug' => 'commerce.manage']);
        $this->assertDatabaseHas('permissions', ['slug' => 'payments.manage']);
    }

    public function test_legacy_admin_role_alone_does_not_grant_super_admin_permissions(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);

        $this->assertFalse($admin->hasRole('super_admin'));
        $this->assertFalse($admin->hasPermission('commerce.manage'));
        $this->assertFalse($admin->hasPermission('settings.manage'));
    }

    public function test_super_admin_role_grants_privileged_permissions(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->hasPermission('commerce.manage'));
        $this->assertTrue($admin->hasPermission('settings.manage'));
    }

    public function test_regular_user_does_not_get_admin_permissions(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->assertFalse($user->hasPermission('commerce.manage'));
    }

    public function test_api_responses_include_a_request_id(): void
    {
        $response = $this->getJson('/api/v1/wear/products');
        $response->assertOk();
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }
}
