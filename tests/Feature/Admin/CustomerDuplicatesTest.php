<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Anti-farm report (bundle item 6): the admin duplicates view groups accounts
 * by signup browser, and the support path gives password-less legacy accounts
 * a password signed by a super administrator only.
 */
class CustomerDuplicatesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_duplicates_page_groups_accounts_by_shared_device(): void
    {
        $shared = hash('sha256', 'shared-browser');

        User::factory()->create(['device_hash' => $shared, 'name' => 'Farm One', 'status' => 'active']);
        User::factory()->create(['device_hash' => $shared, 'name' => 'Farm Two', 'status' => 'active']);
        User::factory()->create(['device_hash' => hash('sha256', 'solo-browser'), 'name' => 'Solo User', 'status' => 'active']);

        $this->actingAs($this->admin())
            ->get(route('admin.wear.customers.duplicates'))
            ->assertOk()
            ->assertSee('Farm One')
            ->assertSee('Farm Two')
            ->assertSee('2 accounts');

        // The solo device is not part of any group.
        $this->actingAs($this->admin())
            ->get(route('admin.wear.customers.duplicates'))
            ->assertDontSee('Solo User');
    }

    public function test_super_admin_can_set_a_customer_password(): void
    {
        $customer = User::factory()->create([
            'status' => 'active',
            'password' => null, // legacy SMS-era account: no password, no self-serve reset
        ]);
        $stolenToken = $customer->createToken('old-session')->plainTextToken;

        $this->actingAs($this->admin())
            ->post(route('admin.wear.customers.password', $customer), [
                'password' => 'rescued-password-123',
                'password_confirmation' => 'rescued-password-123',
            ])
            ->assertRedirect();

        $customer->refresh();
        $this->assertTrue(Hash::check('rescued-password-123', $customer->password));

        // Sessions are revoked so a hijacker's cookie dies with the reset.
        $this->withToken($stolenToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_short_passwords_are_rejected_on_the_support_path(): void
    {
        $customer = User::factory()->create(['status' => 'active']);

        $this->actingAs($this->admin())
            ->post(route('admin.wear.customers.password', $customer), [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertStatus(302)
            ->assertSessionHasErrors('password');
    }

    public function test_non_admins_are_forbidden_from_both_endpoints(): void
    {
        $customer = User::factory()->create(['status' => 'active']);
        $user = User::factory()->create(['role' => UserRole::User, 'status' => 'active']);

        $this->actingAs($user)->get(route('admin.wear.customers.duplicates'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.wear.customers.password', $customer), [
            'password' => 'rescued-password-123',
            'password_confirmation' => 'rescued-password-123',
        ])->assertForbidden();
    }
}
