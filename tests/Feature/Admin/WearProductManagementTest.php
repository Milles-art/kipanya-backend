<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\User;
use App\Models\Wear\WearProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WearProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_create_and_update_wear_product(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.wear.products.store'), [
                'name' => 'Admin Polo',
                'slug' => 'admin-polo',
                'description' => 'A clean test product.',
                'category' => 'Polos',
                'price' => 70000,
                'compare_at_price' => 80000,
                'image_path' => 'assets/wear/catalog/generated/product-13.jpg',
                'badge' => 'NEW',
                'is_active' => '1',
                'is_featured' => '1',
                'sort_order' => 10,
            ])
            ->assertRedirect();

        $product = WearProduct::where('slug', 'admin-polo')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.wear.product.created',
            'auditable_type' => WearProduct::class,
            'auditable_id' => $product->id,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.wear.products.update', $product), [
                'name' => 'Updated Admin Polo',
                'slug' => 'updated-admin-polo',
                'description' => 'Updated.',
                'category' => 'Polos',
                'price' => 75000,
                'compare_at_price' => 85000,
                'image_path' => 'assets/wear/catalog/generated/product-13.jpg',
                'badge' => 'SALE',
                'is_active' => '1',
                'is_featured' => '0',
                'sort_order' => 11,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('wear_products', [
            'id' => $product->id,
            'name' => 'Updated Admin Polo',
            'slug' => 'updated-admin-polo',
            'category' => 'Polos',
        ]);
    }

    public function test_non_admin_cannot_manage_wear_products(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'status' => 'active']);

        $this->actingAs($user)
            ->get(route('admin.wear.products.index'))
            ->assertForbidden();
    }

    public function test_product_with_variants_cannot_be_deleted_from_product_screen(): void
    {
        $admin = $this->admin();

        $product = WearProduct::create([
            'name' => 'Protected Product',
            'slug' => 'protected-product',
            'category' => 'T-Shirts',
            'price' => 50000,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $product->variants()->create([
            'size' => 'M',
            'color' => 'Black',
            'stock' => 5,
            'sku' => 'PROTECTED-M-BLK',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.wear.products.destroy', $product))
            ->assertSessionHasErrors('product');

        $this->assertDatabaseHas('wear_products', ['id' => $product->id]);
    }
}
