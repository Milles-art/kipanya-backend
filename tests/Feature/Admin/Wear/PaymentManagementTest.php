<?php

namespace Tests\Feature\Admin\Wear;

use App\Enums\Auth\UserRole;
use App\Models\Commerce\PaymentTransaction;
use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_payments_and_payment_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $order = WearOrder::create([
            'order_number' => 'KP-TEST-'.strtoupper(substr(uniqid(), -6)),
            'user_id' => $admin->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => '+255700000001',
            'customer_email' => 'customer@example.test',
            'delivery_address' => 'Test Address',
            'delivery_city' => 'Dar es Salaam',
            'subtotal' => 50000,
            'delivery_fee' => 5000,
            'total' => 55000,
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);
        $payment = PaymentTransaction::create([
            'wear_order_id' => $order->id,
            'user_id' => $admin->id,
            'provider' => 'fake',
            'provider_reference' => 'FAKE-123',
            'idempotency_key' => 'test-payment-'.uniqid(),
            'amount' => 55000,
            'currency' => 'TZS',
            'status' => 'pending',
            'initiated_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.wear.payments.index'))
            ->assertOk()->assertSee($order->order_number)->assertSee('FAKE-123');

        $this->actingAs($admin)->get(route('admin.wear.payments.show', $payment))
            ->assertOk()->assertSee('FAKE-123')->assertSee('Test Customer');
    }

    public function test_non_admin_cannot_view_payments(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);
        $this->actingAs($user)->get(route('admin.wear.payments.index'))->assertForbidden();
    }
}
