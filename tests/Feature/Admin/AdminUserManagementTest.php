<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\Administration\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_authorized_admin_can_create_and_update_staff_user(): void
    {
        $admin = $this->admin();
        $role = Role::query()->where('slug', 'commerce_manager')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('Admin Users');
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Store Manager',
            'phone' => '+255710000111',
            'email' => 'store-manager@example.test',
            'role_id' => $role->id,
            'status' => 'active',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('phone', '+255710000111')->firstOrFail();
        $this->assertTrue($user->hasRole('commerce_manager'));
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'admin.users.created', 'auditable_id' => $user->id]);

        $support = Role::query()->where('slug', 'support')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Support Manager',
            'phone' => '+255710000111',
            'email' => 'support-manager@example.test',
            'role_id' => $support->id,
            'status' => 'inactive',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Support Manager', 'status' => 'inactive']);
        $this->assertTrue($user->fresh()->hasRole('support'));
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'admin.users.updated', 'auditable_id' => $user->id]);
    }

    public function test_admin_can_toggle_staff_status_but_cannot_deactivate_self(): void
    {
        $admin = $this->admin();
        $staff = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);
        $staff->roles()->sync([Role::query()->where('slug', 'support')->value('id')]);

        $this->actingAs($admin)->post(route('admin.users.status', $staff))->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'status' => 'inactive']);

        $this->actingAs($admin)->post(route('admin.users.status', $admin))->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'status' => 'active']);
    }

    public function test_non_admin_and_admin_without_permission_are_forbidden(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'status' => 'active']);
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }
}
