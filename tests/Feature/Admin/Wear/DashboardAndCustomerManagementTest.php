<?php

namespace Tests\Feature\Admin\Wear;

use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardAndCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_wear_dashboard_statistics_and_customers(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['name' => 'KP Customer']);

        $product = WearProduct::factory()->create();
        WearProductVariant::factory()->create([
            'wear_product_id' => $product->id,
            'stock' => 12,
        ]);

        WearOrder::factory()->create([
            'user_id' => $customer->id,
            'payment_status' => 'paid',
            'total' => 125000,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk()
            ->assertSee('TZS 125,000')
            ->assertSee('1 paid today')
            ->assertSee('1 variants')
            ->assertSee('12');

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
