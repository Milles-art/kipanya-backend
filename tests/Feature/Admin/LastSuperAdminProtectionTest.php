<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\Administration\Permission;
use App\Models\Administration\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage (M8): the last active super administrator must not be
 * deactivated, while deactivating one of several remains possible.
 */
class LastSuperAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_active_super_admin_cannot_be_deactivated(): void
    {
        // F-04: a non-super `users.manage` holder can no longer touch a super admin at
        // all (403). The "last active super admin" rule remains as a second safety net.
        $actor = $this->managerWithUsersManagePermission();
        $target = User::factory()->admin()->create();

        $this->assertSame(1, $this->activeSuperAdminCount());

        $this->actingAs($actor)
            ->post(route('admin.users.status', $target))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'active']);
    }

    public function test_super_admin_can_be_deactivated_when_another_remains_active(): void
    {
        // Only a super administrator may deactivate another super administrator.
        $actor = User::factory()->admin()->create();
        $target = User::factory()->admin()->create();
        User::factory()->admin()->create();

        $this->assertSame(3, $this->activeSuperAdminCount());

        $this->actingAs($actor)
            ->post(route('admin.users.status', $target))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'inactive']);
    }

    private function activeSuperAdminCount(): int
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', static fn ($query) => $query->where('slug', 'super_admin'))
            ->count();
    }

    private function managerWithUsersManagePermission(): User
    {
        $role = Role::query()->where('slug', 'support')->firstOrFail();
        $permission = Permission::query()->where('slug', 'users.manage')->firstOrFail();
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
        ]);
        $user->roles()->sync([$role->id]);

        return $user->fresh();
    }
}
