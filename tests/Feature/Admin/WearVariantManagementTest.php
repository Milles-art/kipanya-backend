<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\User;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WearVariantManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function product(): WearProduct
    {
        return WearProduct::create([
            'name' => 'Test Tee', 'slug' => 'test-tee', 'category' => 'T-Shirts', 'price' => 70000,
            'is_active' => true, 'is_featured' => false, 'sort_order' => 0,
        ]);
    }

    public function test_admin_can_create_and_update_variant(): void
    {
        $admin = $this->admin();
        $product = $this->product();

        $this->actingAs($admin)->post(route('admin.wear.products.variants.store', $product), [
            'size' => 'M', 'color' => 'Black', 'stock' => 7, 'sku' => 'TEST-TEE-M-BLK',
        ])->assertRedirect();

        $variant = WearProductVariant::where('sku', 'TEST-TEE-M-BLK')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'admin.wear.variant.created', 'auditable_id' => $variant->id]);

        $this->actingAs($admin)->put(route('admin.wear.products.variants.update', [$product, $variant]), [
            'size' => 'L', 'color' => 'Black', 'stock' => 10, 'sku' => 'TEST-TEE-L-BLK',
        ])->assertRedirect();

        $this->assertDatabaseHas('wear_product_variants', ['id' => $variant->id, 'size' => 'L', 'stock' => 10, 'sku' => 'TEST-TEE-L-BLK']);
    }

    public function test_non_admin_cannot_manage_variants(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'status' => 'active']);
        $product = $this->product();

        $this->actingAs($user)->get(route('admin.wear.products.variants.index', $product))->assertForbidden();
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $admin = $this->admin();
        $product = $this->product();
        $product->variants()->create(['size' => 'M', 'color' => 'Black', 'stock' => 3, 'sku' => 'DUP-SKU']);

        $this->actingAs($admin)->post(route('admin.wear.products.variants.store', $product), [
            'size' => 'L', 'color' => 'White', 'stock' => 3, 'sku' => 'DUP-SKU',
        ])->assertSessionHasErrors('sku');
    }
}
