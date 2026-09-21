<?php

namespace Tests\Feature\Foundation;

use App\Models\Administration\AuditLog;
use App\Models\User;
use App\Services\Auth\TotpService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * F-12 (pending 2FA expiry), F-13 (no demo data in production), F-17 (append-only audit log).
 */
final class HardeningMiscTest extends TestCase
{
    use RefreshDatabase;

    private const PENDING = 'admin.two_factor.pending';

    /** @return array{0: User, 1: TotpService} */
    private function adminWithTwoFactor(): array
    {
        $totp = new TotpService;
        $admin = User::factory()->admin()->create();
        $admin->two_factor_secret = $totp->generateSecret();
        $admin->enableTwoFactor();

        return [$admin, $totp];
    }

    // ----------------------------------------------------------------- F-12

    public function test_an_expired_pending_two_factor_state_is_rejected_and_cleared(): void
    {
        [$admin, $totp] = $this->adminWithTwoFactor();

        $this->withSession([self::PENDING => [
            'user_id' => $admin->id,
            'expires_at' => now()->subMinute()->getTimestamp(),
        ]])->post(route('admin.login.two-factor'), ['code' => $totp->code($admin->two_factor_secret)])
            ->assertSessionHasErrors('code')
            ->assertSessionMissing(self::PENDING);

        $this->assertGuest();
    }

    public function test_a_pending_state_without_an_expiry_is_rejected(): void
    {
        [$admin, $totp] = $this->adminWithTwoFactor();

        $this->withSession([self::PENDING => ['user_id' => $admin->id]])
            ->post(route('admin.login.two-factor'), ['code' => $totp->code($admin->two_factor_secret)])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_a_fresh_pending_state_still_completes_the_login(): void
    {
        [$admin, $totp] = $this->adminWithTwoFactor();

        $this->withSession([self::PENDING => [
            'user_id' => $admin->id,
            'expires_at' => now()->addMinutes(4)->getTimestamp(),
        ]])->post(route('admin.login.two-factor'), ['code' => $totp->code($admin->two_factor_secret)])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    // ----------------------------------------------------------------- F-13

    public function test_db_seed_does_not_insert_demo_products_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        // `db:seed` asks for confirmation in production; `--force` skips that, so the
        // seeder itself must refuse to insert sample data.
        $this->app->make(DatabaseSeeder::class)->run();

        $this->assertDatabaseCount('wear_products', 0);
    }

    public function test_db_seed_still_loads_demo_products_outside_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, \App\Models\Wear\WearProduct::query()->count());
    }

    // ----------------------------------------------------------------- F-17

    public function test_audit_log_rows_cannot_be_updated_or_deleted_through_the_model(): void
    {
        $log = AuditLog::create([
            'action' => 'test.action', 'metadata' => ['k' => 'v'], 'created_at' => now(),
        ]);

        $this->assertFalse($log->update(['action' => 'tampered']));
        $this->assertFalse($log->delete());

        $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'action' => 'test.action']);
    }
}
