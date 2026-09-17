<?php

namespace Tests\Feature\Foundation;

use App\Models\Administration\Permission;
use App\Models\Administration\Role;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage (C2/M3): AuthorizationService must defer to the same
 * pivot-backed permission check as the User model. The legacy `role` column is
 * not a source of truth for permissions.
 */
class AuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_admin_without_pivot_role_is_denied(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->assertFalse(app(AuthorizationService::class)->hasPermission($user, 'commerce.manage'));
    }

    public function test_permission_granted_through_a_pivot_role_is_allowed(): void
    {
        $role = Role::query()->where('slug', 'support')->firstOrFail();
        $permission = Permission::query()->where('slug', 'users.manage')->firstOrFail();
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->sync([$role->id]);

        $this->assertTrue(app(AuthorizationService::class)->hasPermission($user->fresh(), 'users.manage'));
    }

    public function test_super_admin_receives_every_permission(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue(app(AuthorizationService::class)->hasPermission($user, 'settings.manage'));
        $this->assertTrue(app(AuthorizationService::class)->hasPermission($user, 'users.manage'));
    }
}
