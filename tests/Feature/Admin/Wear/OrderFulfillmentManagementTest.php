<?php

namespace Tests\Feature\Admin\Wear;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFulfillmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_fulfillment_details(): void
    {
        $admin = User::factory()->admin()->create();
        $order = WearOrder::factory()->create([
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.wear.orders.delivery', $order), [
                'delivery_provider' => 'KP Courier',
                'tracking_number' => 'KP-12345',
                'fulfillment_notes' => 'Call customer on arrival.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('wear_orders', [
            'id' => $order->id,
            'delivery_provider' => 'KP Courier',
            'tracking_number' => 'KP-12345',
            'fulfillment_notes' => 'Call customer on arrival.',
        ]);
    }

    public function test_admin_cannot_cancel_a_paid_confirmed_order_before_refund_workflow_exists(): void
    {
        $admin = User::factory()->admin()->create();
        $order = WearOrder::factory()->create([
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.wear.orders.status', $order), [
                'status' => OrderStatus::Cancelled->value,
                'reason' => 'Customer requested cancellation',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('wear_orders', [
            'id' => $order->id,
            'status' => OrderStatus::Confirmed->value,
            'payment_status' => PaymentStatus::Paid->value,
        ]);
    }

    public function test_status_changes_record_fulfillment_timestamps(): void
    {
        $admin = User::factory()->admin()->create();
        $order = WearOrder::factory()->create([
            'status' => OrderStatus::Processing,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.wear.orders.status', $order), ['status' => OrderStatus::Shipped->value])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Shipped, $order->status);
        $this->assertNotNull($order->shipped_at);

        $this->actingAs($admin)
            ->post(route('admin.wear.orders.status', $order), ['status' => OrderStatus::Delivered->value])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertNotNull($order->delivered_at);
    }
}
