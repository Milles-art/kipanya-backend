<?php

namespace Tests\Feature\Account;

use App\Actions\Auth\IssueSanctumToken;
use App\Models\User;
use App\Models\Wear\WearOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_page_renders_inside_the_account_layout(): void
    {
        $html = $this->get('/wishlist')->assertOk()->getContent();

        $this->assertStringContainsString('Account menu', $html);
        $this->assertStringContainsString('data-wishlist-grid', $html);
        $this->assertStringContainsString('Wishlist', $html);
    }

    public function test_orders_page_exposes_a_track_link_per_order(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = (new IssueSanctumToken)->execute($user);

        WearOrder::create([
            'order_number' => 'KP-3001',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => '+255700000001',
            'delivery_address' => 'Uhuru St 12',
            'subtotal' => 50000,
            'total' => 50000,
        ]);

        $html = $this->withUnencryptedCookies(['kp_web_session' => $token])
            ->get('/account/orders')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('KP-3001', $html);
        $this->assertStringContainsString('Track order', $html);
        $this->assertStringContainsString('/orders/KP-3001', $html);
        $this->assertStringContainsString('data-server-order="KP-3001"', $html);
        $this->assertStringContainsString('Continue payment', $html);
    }

    public function test_order_detail_page_exposes_a_track_button(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $token = (new IssueSanctumToken)->execute($user);

        WearOrder::create([
            'order_number' => 'KP-3002',
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => '+255700000001',
            'delivery_address' => 'Uhuru St 12',
            'subtotal' => 25000,
            'total' => 25000,
        ]);

        $html = $this->withUnencryptedCookies(['kp_web_session' => $token])
            ->get('/account/orders/KP-3002')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Track order', $html);
        $this->assertStringContainsString('/orders/KP-3002', $html);
        $this->assertStringContainsString('data-server-detail', $html);
        $this->assertStringContainsString('data-cancel-order-detail', $html);
    }
}
