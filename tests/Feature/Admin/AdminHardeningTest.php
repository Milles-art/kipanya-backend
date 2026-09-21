<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\Administration\Permission;
use App\Models\Administration\Role;
use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * F-03 (mandatory admin 2FA) and F-04 (users.manage must not reach super admins
 * or change admin login credentials).
 */
final class AdminHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    /** A non-super administrator that has been granted `users.manage`. */
    private function userManager(): User
    {
        $role = Role::query()->where('slug', 'commerce_manager')->firstOrFail();
        $role->permissions()->syncWithoutDetaching([
            Permission::query()->where('slug', 'users.manage')->value('id'),
        ]);

        $manager = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);
        $manager->roles()->sync([$role->id]);

        return $manager;
    }

    private function supportAdmin(): User
    {
        $staff = User::factory()->create(['role' => UserRole::Admin, 'status' => 'active']);
        $staff->roles()->sync([Role::query()->where('slug', 'support')->value('id')]);

        return $staff;
    }

    // ---------------------------------------------------------------- F-03

    public function test_admin_without_two_factor_is_forced_to_enrol_when_enforced(): void
    {
        config(['security.admin_require_2fa' => true]);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.security.two-factor.index'));

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertRedirect(route('admin.security.two-factor.index'));
    }

    public function test_enrolment_screen_and_logout_remain_reachable_when_enforced(): void
    {
        config(['security.admin_require_2fa' => true]);
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.security.two-factor.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.logout'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_with_two_factor_enabled_passes_the_gate(): void
    {
        config(['security.admin_require_2fa' => true]);
        $admin = $this->superAdmin();
        $admin->two_factor_secret = (new TotpService)->generateSecret();
        $admin->enableTwoFactor();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_enforcement_can_be_switched_off(): void
    {
        config(['security.admin_require_2fa' => false]);

        $this->actingAs($this->superAdmin())->get(route('admin.dashboard'))->assertOk();
    }

    // ---------------------------------------------------------------- F-04

    public function test_user_manager_cannot_view_edit_update_or_deactivate_a_super_admin(): void
    {
        $manager = $this->userManager();
        $target = $this->superAdmin();
        $this->superAdmin(); // a second super admin so the "last super admin" guard is not what blocks us

        $this->actingAs($manager)->get(route('admin.users.edit', $target))->assertForbidden();

        $this->actingAs($manager)->put(route('admin.users.update', $target), [
            'name' => 'Pwned',
            'phone' => $target->phone,
            'email' => $target->email,
            'role_id' => Role::query()->where('slug', 'support')->value('id'),
            'status' => 'inactive',
        ])->assertForbidden();

        $this->actingAs($manager)->post(route('admin.users.status', $target))->assertForbidden();

        $target->refresh();
        $this->assertSame('active', $target->status);
        $this->assertTrue($target->hasRole('super_admin'));
        $this->assertNotSame('Pwned', $target->name);
    }

    public function test_user_manager_cannot_change_another_admins_phone_or_email(): void
    {
        $manager = $this->userManager();
        $staff = $this->supportAdmin();
        $originalPhone = $staff->phone;

        $this->actingAs($manager)->put(route('admin.users.update', $staff), [
            'name' => $staff->name,
            'phone' => '+255700000999',
            'email' => $staff->email,
            'role_id' => Role::query()->where('slug', 'support')->value('id'),
            'status' => 'active',
        ])->assertSessionHasErrors('phone');

        $this->assertSame($originalPhone, $staff->fresh()->phone);
    }

    public function test_user_manager_can_still_rename_and_change_the_role_of_a_normal_admin(): void
    {
        $manager = $this->userManager();
        $staff = $this->supportAdmin();

        $this->actingAs($manager)->put(route('admin.users.update', $staff), [
            'name' => 'Renamed Support',
            'phone' => $staff->phone,
            'email' => $staff->email,
            'role_id' => Role::query()->where('slug', 'commerce_manager')->value('id'),
            'status' => 'active',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertSame('Renamed Support', $staff->fresh()->name);
        $this->assertTrue($staff->fresh()->hasRole('commerce_manager'));
    }

    public function test_super_admin_can_change_a_phone_and_it_resets_two_factor(): void
    {
        $super = $this->superAdmin();
        $staff = $this->supportAdmin();
        $staff->two_factor_secret = (new TotpService)->generateSecret();
        $staff->enableTwoFactor();

        $this->actingAs($super)->put(route('admin.users.update', $staff), [
            'name' => $staff->name,
            'phone' => '+255700000888',
            'email' => $staff->email,
            'role_id' => Role::query()->where('slug', 'support')->value('id'),
            'status' => 'active',
        ])->assertRedirect(route('admin.users.index'));

        $staff->refresh();
        $this->assertSame('+255700000888', $staff->phone);
        $this->assertFalse($staff->twoFactorEnabled(), 'A changed credential must force re-enrolment of 2FA.');
    }

    public function test_super_admin_can_still_manage_another_super_admin(): void
    {
        $super = $this->superAdmin();
        $other = $this->superAdmin();

        $this->actingAs($super)->post(route('admin.users.status', $other))->assertRedirect();

        $this->assertSame('inactive', $other->fresh()->status);
    }
}
