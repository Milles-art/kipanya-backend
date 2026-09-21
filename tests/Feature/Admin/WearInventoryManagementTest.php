<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Enums\Commerce\ReservationStatus;
use App\Models\Commerce\StockReservation;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WearInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function variant(int $stock = 10): WearProductVariant
    {
        $product = WearProduct::create([
            'name' => 'Inventory Tee', 'slug' => 'inventory-tee', 'category' => 'T-Shirts', 'price' => 42000,
            'is_active' => true, 'is_featured' => false, 'sort_order' => 0,
        ]);

        return $product->variants()->create(['size' => 'M', 'color' => 'Black', 'stock' => $stock, 'sku' => 'INV-TEE-M-BLK']);
    }

    public function test_admin_can_view_inventory_and_update_stock(): void
    {
        $admin = $this->admin();
        $variant = $this->variant(10);

        $this->actingAs($admin)
            ->get(route('admin.wear.inventory.index'))
            ->assertOk()
            ->assertSee('Inventory')
            ->assertSee($variant->sku);

        $this->actingAs($admin)
            ->post(route('admin.wear.inventory.stock', $variant), ['stock' => 18, 'reason' => 'Physical count'])
            ->assertRedirect();

        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'stock' => 18]);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'admin.wear.inventory.stock_updated', 'auditable_id' => $variant->id]);
    }

    public function test_stock_cannot_be_set_below_active_reserved_quantity(): void
    {
        $admin = $this->admin();
        $variant = $this->variant(10);
        $order = WearOrder::create([
            'order_number' => 'KP-INV-TEST',
            'customer_name' => 'Test Customer',
            'customer_phone' => '+255700000001',
            'delivery_address' => 'Test Address',
            'subtotal' => 42000,
            'delivery_fee' => 0,
            'total' => 42000,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);
        $reservation = StockReservation::create([
            'wear_order_id' => $order->id,
            'status' => ReservationStatus::Active,
            'expires_at' => now()->addMinutes(10),
        ]);
        $reservation->items()->create(['wear_product_variant_id' => $variant->id, 'quantity' => 6]);

        $this->actingAs($admin)
            ->post(route('admin.wear.inventory.stock', $variant), ['stock' => 5])
            ->assertStatus(422);

        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    public function test_non_admin_cannot_manage_inventory(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'status' => 'active']);
        $variant = $this->variant();

        $this->actingAs($user)->get(route('admin.wear.inventory.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.wear.inventory.stock', $variant), ['stock' => 5])->assertForbidden();
    }
}
