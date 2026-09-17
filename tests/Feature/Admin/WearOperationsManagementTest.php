<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\ContactMessage;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\Wear\WearReturnRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WearOperationsManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_manage_collections_returns_and_enquiries(): void
    {
        $admin = $this->admin();
        $product = WearProduct::factory()->create(['is_active' => true]);
        $order = WearOrder::factory()->create(['user_id' => $admin->id, 'status' => 'delivered']);
        $item = $order->items()->first();
        $return = WearReturnRequest::create([
            'wear_order_id' => $order->id, 'user_id' => $admin->id, 'request_type' => 'return',
            'reason' => 'wrong_size', 'order_item_ids' => [$item?->id], 'status' => 'under_review',
        ]);
        $message = ContactMessage::create(['name' => 'Customer', 'email' => 'c@example.com', 'type' => 'order', 'message' => 'Need help with my order.', 'status' => 'new']);

        $this->actingAs($admin)->get(route('admin.wear.collections.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.wear.collections.store'), ['name' => 'New Drop', 'slug' => 'new-drop', 'description' => 'Latest', 'sort_order' => 1, 'is_active' => 1, 'product_ids' => [$product->id]])->assertRedirect();
        $this->assertDatabaseHas('wear_collections', ['slug' => 'new-drop']);
        $this->actingAs($admin)->get(route('admin.wear.returns.show', $return))->assertOk();
        $this->actingAs($admin)->post(route('admin.wear.returns.status', $return), ['status' => 'approved', 'notes' => 'Approved'])->assertRedirect();
        $this->assertDatabaseHas('wear_return_requests', ['id' => $return->id, 'status' => 'approved']);
        $this->actingAs($admin)->get(route('admin.wear.enquiries.show', $message))->assertOk();
        $this->actingAs($admin)->post(route('admin.wear.enquiries.status', $message), ['status' => 'resolved'])->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['id' => $message->id, 'status' => 'resolved']);
    }

    public function test_non_admin_cannot_manage_operations(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'status' => 'active']);
        $this->actingAs($user)->get(route('admin.wear.collections.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.wear.returns.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.wear.enquiries.index'))->assertForbidden();
    }

    public function test_inventory_adjustments_are_recorded(): void
    {
        $admin = $this->admin();
        $product = WearProduct::factory()->create(['is_active' => true]);
        $variant = WearProductVariant::factory()->create(['wear_product_id' => $product->id, 'stock' => 10]);
        $this->actingAs($admin)->post(route('admin.wear.inventory.stock', $variant), ['stock' => 15, 'reason' => 'Restock'])->assertRedirect();
        $this->assertDatabaseHas('wear_inventory_movements', ['wear_product_variant_id' => $variant->id, 'quantity' => 5, 'stock_before' => 10, 'stock_after' => 15, 'created_by' => $admin->id]);
    }
}
