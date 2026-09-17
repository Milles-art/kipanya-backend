<?php

namespace Tests\Feature\Admin;

use App\Models\Administration\AuditLog;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditLogManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_filter_audit_logs(): void
    {
        $admin = User::factory()->admin()->create();
        $actor = User::factory()->create(['name' => 'Store Operator', 'email' => 'operator@example.test']);

        AuditLog::create([
            'actor_id' => $actor->id,
            'action' => 'admin.wear.product.updated',
            'auditable_type' => 'App\\Models\\Wear\\WearProduct',
            'auditable_id' => 12,
            'metadata' => ['name' => 'Classic Tee'],
            'ip_address' => '127.0.0.1',
            'request_id' => '11111111-1111-1111-1111-111111111111',
            'created_at' => now(),
        ]);

        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'admin.wear.inventory.stock_updated',
            'auditable_type' => 'App\\Models\\Wear\\WearVariant',
            'auditable_id' => 8,
            'metadata' => ['quantity' => 20],
            'ip_address' => '127.0.0.1',
            'request_id' => '22222222-2222-2222-2222-222222222222',
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit.index', ['q' => 'product.updated']))
            ->assertOk()
            ->assertSee('admin.wear.product.updated')
            ->assertDontSee('admin.wear.inventory.stock_updated');
    }

    public function test_admin_can_view_audit_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $log = app(AuditLogger::class)->log(
            request(),
            'admin.settings.updated',
            null,
            ['changed' => ['currency' => ['from' => 'TZS', 'to' => 'USD']]],
        );

        $this->actingAs($admin)
            ->get(route('admin.audit.show', $log))
            ->assertOk()
            ->assertSee('admin.settings.updated')
            ->assertSee('currency');
    }

    public function test_non_admin_cannot_view_audit_logs(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.audit.index'))
            ->assertForbidden();
    }
}
