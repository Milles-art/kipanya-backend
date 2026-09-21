<?php

namespace Tests\Feature\Admin\Wear;

use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Enums\Commerce\ReservationStatus;
use App\Models\Commerce\StockReservation;
use App\Models\Commerce\StockReservationItem;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
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

    public function test_cancelling_a_pending_payment_order_releases_the_stock_reservation(): void
    {
        $admin = User::factory()->admin()->create();
        $order = WearOrder::factory()->create([
            'status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
        ]);
        $product = WearProduct::create(['name' => 'Release Tee', 'slug' => 'release-tee-'.uniqid(), 'price' => 25000, 'category' => 'shirts', 'is_active' => true]);
        $variant = WearProductVariant::create(['wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black', 'stock' => 1, 'sku' => 'REL-'.strtoupper(bin2hex(random_bytes(4)))]);
        $reservation = StockReservation::create(['wear_order_id' => $order->id, 'status' => ReservationStatus::Active, 'expires_at' => now()->addMinutes(15)]);
        StockReservationItem::create(['reservation_id' => $reservation->id, 'wear_product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->actingAs($admin)
            ->post(route('admin.wear.orders.status', $order), [
                'status' => OrderStatus::Cancelled->value,
                'reason' => 'Customer could not complete payment',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('wear_orders', ['id' => $order->id, 'status' => OrderStatus::Cancelled->value]);
        $this->assertDatabaseHas('wear_stock_reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::Released->value,
        ]);
    }
}
