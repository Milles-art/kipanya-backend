<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\User;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_all_admin_index_and_create_pages_render(): void
    {
        $admin = $this->admin();

        foreach ([
            '/admin',
            '/admin/audit',
            '/admin/settings',
            '/admin/storefront',
            '/admin/users',
            '/admin/users/create',
            '/admin/wear/analytics',
            '/admin/wear/categories',
            '/admin/wear/collections',
            '/admin/wear/collections/create',
            '/admin/wear/products',
            '/admin/wear/products/create',
            '/admin/wear/inventory',
            '/admin/wear/orders',
            '/admin/wear/payments',
            '/admin/wear/returns',
            '/admin/wear/enquiries',
            '/admin/wear/customers',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk("GET {$url} must render");
        }
    }

    public function test_admin_login_page_renders_for_guests(): void
    {
        // Signed-in admins are redirected away from the login page, so the
        // render check runs unauthenticated.
        $this->get('/admin/login')->assertOk()->assertSee('Administrator sign in', false);
    }

    public function test_admin_edit_pages_render(): void
    {
        $admin = $this->admin();
        // User management only handles administrator targets (see
        // ensureManageable), so the edit target must be an admin too.
        $user = User::factory()->admin()->create();

        $product = WearProduct::factory()->create();
        $variant = WearProductVariant::factory()->create(['wear_product_id' => $product->id]);
        $collection = WearCollection::create([
            'name' => 'Essentials', 'slug' => 'essentials', 'is_active' => true,
        ]);
        $message = ContactMessage::create([
            'name' => 'Amina', 'email' => 'amina@example.com',
            'type' => 'support', 'message' => 'Hello', 'status' => 'new',
        ]);

        $this->actingAs($admin)->get("/admin/users/{$user->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/wear/products/{$product->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/wear/collections/{$collection->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/wear/products/{$product->id}/variants")->assertOk();
        $this->actingAs($admin)->get("/admin/wear/products/{$product->id}/variants/create")->assertOk();
        $this->actingAs($admin)->get("/admin/wear/products/{$product->id}/variants/{$variant->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/admin/wear/enquiries/{$message->id}")->assertOk();
    }

    public function test_admin_order_show_page_renders(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();

        $order = WearOrder::factory()->create(['user_id' => $customer->id]);

        $this->actingAs($admin)->get("/admin/wear/orders/{$order->id}")->assertOk()
            ->assertSee($order->order_number);
    }
}
