<?php

namespace Tests\Feature\Admin\Wear;

use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardAndCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_wear_dashboard_statistics_and_customers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['name' => 'KP Customer']);

        WearOrder::factory()->create([
            'user_id' => $customer->id,
            'payment_status' => 'paid',
            'total' => 125000,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('Store counters')
            ->assertSee('Paid orders')
            ->assertSee('data-stat-counter="1"', false);

        $this->actingAs($admin)->get('/admin/wear/customers')->assertOk()
            ->assertSee('Customers')
            ->assertSee('KP Customer');
    }

    public function test_non_admin_cannot_view_customers(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin/wear/customers')->assertForbidden();
    }
}
