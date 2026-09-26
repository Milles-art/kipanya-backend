<?php

namespace Tests\Feature\Account;

use App\Actions\Auth\IssueSanctumToken;
use App\Enums\Auth\UserStatus;
use App\Enums\Commerce\OrderStatus;
use App\Models\Auth\NotificationPreference;
use App\Models\Auth\UserProfile;
use App\Models\Commerce\Address;
use App\Models\Commerce\LoyaltyAccount;
use App\Models\Commerce\LoyaltyTransaction;
use App\Models\Commerce\PaymentMethod;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountPagesRenderRealDataTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): array
    {
        $user = User::factory()->create([
            'name' => 'Mariam Juma',
            'email' => 'mariam.example@kipanya.tz',
            'phone' => '+255724000001',
            'status' => UserStatus::Active,
        ]);
        $token = (new IssueSanctumToken)->execute($user);

        return [$user, $token];
    }

    private function seedData(User $user): WearOrder
    {
        $address = Address::create([
            'user_id' => $user->id,
            'type' => 'shipping',
            'label' => 'Home',
            'recipient_name' => 'Mariam Juma',
            'phone' => '+255724000001',
            'region' => 'Dar es Salaam',
            'district' => 'Ilala',
            'ward' => 'Kariakoo',
            'street' => 'Pugu Road 12',
            'notes' => null,
            'is_default' => true,
        ]);

        PaymentMethod::create([
            'user_id' => $user->id,
            'brand' => 'Visa',
            'last4' => '4242',
            'exp_month' => '09',
            'exp_year' => '2029',
            'is_default' => true,
        ]);

        $loyalty = LoyaltyAccount::create(['user_id' => $user->id, 'points' => 120, 'lifetime_points' => 120]);
        LoyaltyTransaction::create([
            'user_id' => $user->id,
            'loyalty_account_id' => $loyalty->id,
            'points' => 120,
            'type' => 'earn',
            'description' => 'Order points',
            'meta' => ['order_number' => 'KP-2001'],
        ]);

        NotificationPreference::create([
            'user_id' => $user->id,
            'push_enabled' => true,
            'email_enabled' => false,
            'sms_enabled' => true,
            'marketing_enabled' => true,
            'restock_enabled' => false,
            'price_drop_enabled' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'size_profile' => ['top' => 'L', 'bottom' => '32', 'fit' => 'Regular', 'height_cm' => 178],
        ]);

        $order = WearOrder::create([
            'order_number' => 'KP-2001',
            'user_id' => $user->id,
            'customer_name' => 'Mariam Juma',
            'customer_phone' => '+255724000001',
            'customer_email' => 'mariam.example@kipanya.tz',
            'delivery_address' => 'Pugu Road 12, Kariakoo, Ilala, Dar es Salaam',
            'delivery_city' => 'Dar es Salaam',
            'subtotal' => 65000,
            'delivery_fee' => 3000,
            'total' => 68000,
            'status' => OrderStatus::Delivered,
            'payment_status' => 'paid',
            'payment_method' => 'mobile_money',
            'placed_at' => now()->subDays(5),
            'delivered_at' => now()->subDays(2),
        ]);

        WearOrderItem::create([
            'wear_order_id' => $order->id,
            'product_name' => 'Crimson Kanga Dress',
            'sku' => 'KNG-RED-1',
            'size' => 'L',
            'color' => 'Crimson',
            'quantity' => 1,
            'unit_price' => 65000,
            'line_total' => 68000,
        ]);

        $user->refresh();
        $user->load(['addresses', 'paymentMethods', 'loyaltyAccount', 'notificationPreferences', 'profile']);

        return $order;
    }

    private function request(string $token): self
    {
        return $this->withUnencryptedCookies(['kp_web_session' => $token]);
    }

    public function test_dashboard_renders_real_user_and_order_data(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account')
            ->assertOk()
            ->assertSee('Mariam Juma')
            ->assertSee('+255724000001')
            ->assertSee('KP-2001')
            ->assertSee('Pugu Road 12')
            ->assertSee('4242')
            ->assertSee('120');
    }

    public function test_orders_page_renders_real_orders(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/orders')
            ->assertOk()
            ->assertSee('KP-2001')
            ->assertSee('Crimson Kanga Dress')
            ->assertSee('KP-2001', (bool) \Illuminate\Support\Str::contains('x', 'x'));
    }

    public function test_order_detail_renders_real_order(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/orders/KP-2001')
            ->assertOk()
            ->assertSee('KP-2001')
            ->assertSee('Crimson Kanga Dress')
            ->assertSee('68,000');
    }

    public function test_addresses_page_renders_real_address(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/addresses')
            ->assertOk()
            ->assertSee('Mariam Juma')
            ->assertSee('Pugu Road 12')
            ->assertSee('Kariakoo');
    }

    public function test_payment_methods_page_renders_real_card(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/payment-methods')
            ->assertOk()
            ->assertSee('4242')
            ->assertSee('Visa');
    }

    public function test_loyalty_page_renders_real_points_and_history(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/loyalty')
            ->assertOk()
            ->assertSee('120')
            ->assertSee('Order points');
    }

    public function test_profile_page_prefills_real_user_data(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/profile')
            ->assertOk()
            ->assertSee('Mariam Juma')
            ->assertSee('mariam.example@kipanya.tz');
    }

    public function test_security_page_renders_sessions(): void
    {
        [$user, $token] = $this->signedInUser();

        $this->request($token)
            ->get('/account/security')
            ->assertOk()
            ->assertSee('data-revoke-session');
    }

    public function test_notifications_page_reflects_preferences(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/notifications')
            ->assertOk()
            ->assertSee('name="notify_promotions" checked', false)
            ->assertSee('name="channel_sms" checked', false)
            ->assertSee('name="channel_email" >', false);
    }

    public function test_returns_page_renders_eligible_orders(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/returns')
            ->assertOk()
            ->assertSee('KP-2001');
    }

    public function test_size_profile_page_preselects_values(): void
    {
        [$user, $token] = $this->signedInUser();
        $this->seedData($user);

        $this->request($token)
            ->get('/account/size-profile')
            ->assertOk()
            ->assertSee('selected')
            ->assertSee('178');
    }
}