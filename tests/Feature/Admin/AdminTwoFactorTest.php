<?php

namespace Tests\Feature\Admin;

use App\Models\Administration\AuditLog;
use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)).'@example.test',
        ]);
        $admin->forceFill(['password' => 'correct-horse-123'])->save();

        return $admin->fresh();
    }

    private function credentials(User $admin, string $password = 'correct-horse-123'): array
    {
        return ['email' => $admin->email, 'password' => $password];
    }

    /**
     * @return array{0: User, 1: TotpService}
     */
    private function adminWithTwoFactor(): array
    {
        $totp = new TotpService;
        $admin = $this->admin();
        $admin->two_factor_secret = $totp->generateSecret();
        $admin->enableTwoFactor();

        return [$admin, $totp];
    }

    public function test_admin_without_two_factor_signs_in_directly(): void
    {
        $admin = $this->admin();

        $this->from('/admin/login')
            ->post('/admin/login', $this->credentials($admin))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_with_two_factor_must_confirm_code_before_signing_in(): void
    {
        [$admin, $totp] = $this->adminWithTwoFactor();

        $this->from('/admin/login')
            ->post('/admin/login', $this->credentials($admin))
            ->assertRedirect('/admin/login')
            ->assertSessionHas('two_factor_required');

        $this->assertGuest('web');

        $this->from('/admin/login')
            ->post('/admin/login/two-factor', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest('web');

        $this->from('/admin/login')
            ->post('/admin/login/two-factor', ['code' => $totp->code($admin->refresh()->two_factor_secret)])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(session()->has('admin.two_factor.pending'));
    }

    public function test_admin_login_rejects_a_wrong_password_without_revealing_which_part_failed(): void
    {
        $admin = $this->admin();

        $this->from('/admin/login')
            ->post('/admin/login', $this->credentials($admin, 'wrong-password-xyz'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_admin_login_rejects_unknown_emails_with_the_same_message(): void
    {
        $this->from('/admin/login')
            ->post('/admin/login', ['email' => 'ghost@example.test', 'password' => 'wrong-password-xyz'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('web');
    }

    public function test_two_factor_enrollment_requires_a_valid_code(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/security/two-factor')
            ->assertOk()
            ->assertSee('Two-Factor Authentication');

        $secret = $admin->refresh()->two_factor_secret;
        $this->assertNotEmpty($secret);

        $totp = new TotpService;

        $this->actingAs($admin)
            ->post('/admin/security/two-factor/enable', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($admin->refresh()->twoFactorEnabled());

        $this->actingAs($admin)
            ->from('/admin/security/two-factor')
            ->post('/admin/security/two-factor/enable', ['code' => $totp->code($secret)])
            ->assertRedirect('/admin/security/two-factor')
            ->assertSessionHas('success');

        $this->assertTrue($admin->refresh()->twoFactorEnabled());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.security.two_factor.enabled',
        ]);
        $this->assertNotNull(AuditLog::where('actor_id', $admin->id)->latest('created_at')->first());
    }

    public function test_two_factor_can_be_disabled_with_a_valid_code(): void
    {
        [$admin, $totp] = $this->adminWithTwoFactor();

        $this->actingAs($admin)
            ->post('/admin/security/two-factor/disable', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertTrue($admin->refresh()->twoFactorEnabled());

        $this->actingAs($admin)
            ->from('/admin/security/two-factor')
            ->post('/admin/security/two-factor/disable', ['code' => $totp->code($admin->fresh()->two_factor_secret)])
            ->assertRedirect('/admin/security/two-factor')
            ->assertSessionHas('success');

        $this->assertFalse($admin->refresh()->twoFactorEnabled());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.security.two_factor.disabled',
        ]);
    }
}
