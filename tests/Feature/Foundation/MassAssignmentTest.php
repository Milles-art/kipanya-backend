<?php

namespace Tests\Feature\Foundation;

use App\Enums\Auth\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage: `role` and `status` are security-sensitive and must not
 * be mass-assignable, so validated request payloads cannot escalate accounts.
 */
class MassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_and_status_cannot_be_mass_assigned_on_create(): void
    {
        $user = User::create([
            'name' => 'Escalation Attempt',
            'phone' => '+255700000010',
            'role' => UserRole::Admin->value,
            'status' => 'inactive',
        ]);

        $user->refresh();

        $this->assertSame(UserRole::User, $user->role);
        $this->assertSame('active', $user->status);
    }

    public function test_role_and_status_cannot_be_mass_assigned_on_update(): void
    {
        $user = User::factory()->create();

        $user->update([
            'name' => 'Renamed',
            'role' => UserRole::Admin->value,
            'status' => 'inactive',
        ]);

        $user->refresh();

        $this->assertSame('Renamed', $user->name);
        $this->assertSame(UserRole::User, $user->role);
        $this->assertSame('active', $user->status);
    }

    public function test_role_and_status_can_still_be_assigned_explicitly(): void
    {
        $user = User::factory()->create();

        $user->forceFill(['role' => UserRole::Admin, 'status' => 'inactive'])->save();

        $user->refresh();

        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertSame('inactive', $user->status);
    }
}
