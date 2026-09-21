<?php

namespace Tests\Feature\Foundation;

use App\Enums\Auth\UserRole;
use App\Models\Administration\Permission;
use App\Models\Administration\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the hardening pass:
 *  - the legacy `role` column is no longer an admin source of truth;
 *  - `super_admin` can only be assigned by an existing super administrator;
 *  - responses ship a nonce-based script-src without 'unsafe-inline'.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_admin_role_column_alone_is_not_an_admin(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);

        $this->assertFalse($user->isAdmin());
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_user_manager_without_super_admin_cannot_assign_super_admin(): void
    {
        $actor = $this->managerWithUsersManagePermission();
        $superAdmin = Role::query()->where('slug', 'super_admin')->firstOrFail();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name' => 'Escalation Attempt',
                'phone' => '+255710000222',
                'email' => 'escalation@example.test',
                'role_id' => $superAdmin->id,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => 'escalation@example.test']);
    }

    public function test_super_admin_can_assign_super_admin(): void
    {
        $actor = User::factory()->admin()->create();
        $superAdmin = Role::query()->where('slug', 'super_admin')->firstOrFail();

        $this->actingAs($actor)
            ->post(route('admin.users.store'), [
                'name' => 'Second Super Admin',
                'phone' => '+255710000333',
                'email' => 'second-super@example.test',
                'role_id' => $superAdmin->id,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.users.index'));

        $created = User::query()->where('email', 'second-super@example.test')->firstOrFail();
        $this->assertTrue($created->hasRole('super_admin'));
    }

    public function test_responses_use_a_script_nonce_without_unsafe_inline(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression("/script-src [^;]*'nonce-[^']+'/", $policy);
        $this->assertDoesNotMatchRegularExpression("/script-src [^;]*'unsafe-inline'/", $policy);
        $this->assertStringContainsString("worker-src 'none'", $policy);
        $this->assertStringContainsString("manifest-src 'self'", $policy);
        $this->assertStringContainsString("frame-src 'none'", $policy);
        $this->assertMatchesRegularExpression('/<script nonce="[^"]+">/', (string) $response->getContent());
    }

    private function managerWithUsersManagePermission(): User
    {
        $role = Role::query()->where('slug', 'support')->firstOrFail();
        $permission = Permission::query()->where('slug', 'users.manage')->firstOrFail();
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $user = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);
        $user->roles()->sync([$role->id]);

        return $user->fresh();
    }
}
