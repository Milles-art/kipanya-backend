<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\Administration\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_authorized_admin_can_view_and_update_store_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Store settings');

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'store_name' => 'Kipanya Wear TZ',
                'store_tagline' => 'Wear your story.',
                'support_email' => 'support@kipanyawear.test',
                'support_phone' => '+255700000000',
                'currency' => 'TZS',
                'timezone' => 'Africa/Dar_es_Salaam',
            ])
            ->assertRedirect('/admin/settings')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('store_settings', [
            'key' => 'store_name',
            'value' => 'Kipanya Wear TZ',
        ]);

        $this->assertDatabaseHas('store_settings', [
            'key' => 'support_email',
            'value' => 'support@kipanyawear.test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.settings.updated',
        ]);

        $this->assertNotNull(AuditLog::where('actor_id', $admin->id)->latest('created_at')->first());
    }

    public function test_settings_validation_rejects_invalid_values(): void
    {
        $this->actingAs($this->admin())
            ->put('/admin/settings', [
                'store_name' => '',
                'store_tagline' => 'Valid',
                'support_email' => 'not-an-email',
                'support_phone' => '+255700000000',
                'currency' => 'EUR',
                'timezone' => 'Africa/Dar_es_Salaam',
            ])
            ->assertSessionHasErrors(['store_name', 'support_email', 'currency']);
    }

    public function test_non_admin_cannot_manage_settings(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertForbidden();

        $this->assertDatabaseHas('store_settings', [
            'key' => 'store_name',
            'value' => 'Kipanya Wear',
        ]);
    }
}
