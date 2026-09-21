<?php

namespace Tests\Feature\Admin\Wear;

use App\Enums\Auth\UserRole;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearOrderItem;
use App\Models\Wear\WearProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_wear_analytics_for_paid_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $product = WearProduct::create([
            'name' => 'Analytics Tee', 'slug' => 'analytics-tee', 'category' => 'T-Shirts',
            'price' => 25000, 'is_active' => true, 'is_featured' => false,
        ]);
        $paid = WearOrder::create([
            'order_number' => 'KP-ANALYTICS-'.uniqid(), 'user_id' => $admin->id,
            'customer_name' => 'Analytics Customer', 'customer_phone' => '+255700000001',
            'delivery_address' => 'Analytics delivery address', 'delivery_city' => 'Dar es Salaam',
            'subtotal' => 50000, 'delivery_fee' => 5000, 'total' => 55000,
            'status' => 'delivered', 'payment_status' => 'paid', 'placed_at' => now(),
        ]);
        $pending = WearOrder::create([
            'order_number' => 'KP-ANALYTICS-PENDING-'.uniqid(), 'user_id' => $admin->id,
            'customer_name' => 'Pending Customer', 'customer_phone' => '+255700000002', 'delivery_address' => 'Pending delivery address', 'delivery_city' => 'Dar es Salaam', 'subtotal' => 25000, 'delivery_fee' => 0, 'total' => 25000,
            'status' => 'pending_payment', 'payment_status' => 'pending', 'placed_at' => now(),
        ]);
        WearOrderItem::create([
            'wear_order_id' => $paid->id, 'wear_product_id' => $product->id,
            'product_name' => $product->name, 'quantity' => 2, 'unit_price' => 25000, 'line_total' => 50000,
        ]);
        WearOrderItem::create([
            'wear_order_id' => $pending->id, 'wear_product_id' => $product->id,
            'product_name' => $product->name, 'quantity' => 1, 'unit_price' => 25000, 'line_total' => 25000,
        ]);

        $this->actingAs($admin)->get(route('admin.wear.analytics.index'))
            ->assertOk()
            ->assertSee('Store analytics')
            ->assertSee('TZS 55,000')
            ->assertSee('Analytics Tee')
            ->assertSee('2 unit(s)');
    }

    public function test_non_admin_cannot_view_wear_analytics(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user)->get(route('admin.wear.analytics.index'))->assertForbidden();
    }
}
