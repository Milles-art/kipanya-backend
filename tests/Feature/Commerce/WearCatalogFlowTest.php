<?php

namespace Tests\Feature\Commerce;

use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WearCatalogFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_returns_only_active_products_with_variants(): void
    {
        $active = WearProduct::create([
            'name' => 'Active Tee',
            'slug' => 'active-tee',
            'price' => 35000,
            'category' => 'T-Shirts',
            'image_path' => 'assets/wear/catalog/essential-black-tee.jpg',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        WearProductVariant::create([
            'wear_product_id' => $active->id,
            'size' => 'M',
            'color' => 'Black',
            'stock' => 4,
            'sku' => 'ACTIVE-TEE-M-BLK',
        ]);

        WearProduct::create([
            'name' => 'Hidden Tee',
            'slug' => 'hidden-tee',
            'price' => 30000,
            'category' => 'T-Shirts',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->getJson('/api/v1/wear/products');

        $response->assertOk()
            ->assertJsonPath('data.0.slug', 'active-tee')
            ->assertJsonPath('data.0.variants.0.size', 'M')
            ->assertJsonPath('data.0.variants.0.stock', 4)
            ->assertJsonPath('data.0.availability', 'in_stock');

        $response->assertJsonMissingPath('data.1');
    }

    public function test_catalog_can_filter_by_category_and_featured(): void
    {
        WearProduct::create([
            'name' => 'Featured Polo',
            'slug' => 'featured-polo',
            'price' => 42000,
            'category' => 'Polos',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        WearProduct::create([
            'name' => 'Regular Polo',
            'slug' => 'regular-polo',
            'price' => 40000,
            'category' => 'Polos',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        WearProduct::create([
            'name' => 'Featured Tee',
            'slug' => 'featured-tee',
            'price' => 36000,
            'category' => 'T-Shirts',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $this->getJson('/api/v1/wear/products?category=Polos&featured=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'featured-polo');
    }

    public function test_catalog_accepts_category_slug(): void
    {
        WearProduct::create([
            'name' => 'Slug Tee',
            'slug' => 'slug-tee',
            'price' => 30000,
            'category' => 'T-Shirts',
            'is_active' => true,
        ]);

        WearProduct::create([
            'name' => 'Slug Hoodie',
            'slug' => 'slug-hoodie',
            'price' => 60000,
            'category' => 'Hoodies',
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/wear/products?category=t-shirts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'slug-tee');
    }

    public function test_product_detail_uses_slug_and_hides_inactive_products(): void
    {
        $product = WearProduct::create([
            'name' => 'Detail Hoodie',
            'slug' => 'detail-hoodie',
            'price' => 55000,
            'category' => 'Hoodies',
            'description' => 'Premium hoodie.',
            'compare_at_price' => 65000,
            'is_active' => true,
        ]);

        WearProductVariant::create([
            'wear_product_id' => $product->id,
            'size' => 'L',
            'color' => 'Navy',
            'stock' => 0,
            'sku' => 'DETAIL-HOODIE-L-NAVY',
        ]);

        $this->getJson('/api/v1/wear/products/detail-hoodie')
            ->assertOk()
            ->assertJsonPath('data.name', 'Detail Hoodie')
            ->assertJsonPath('data.price', 55000)
            ->assertJsonPath('data.compare_at_price', 65000)
            ->assertJsonPath('data.variants.0.in_stock', false)
            ->assertJsonPath('data.availability', 'out_of_stock');

        $product->update(['is_active' => false]);

        $this->getJson('/api/v1/wear/products/detail-hoodie')
            ->assertNotFound();
    }

    public function test_category_endpoint_returns_distinct_active_categories(): void
    {
        WearProduct::create([
            'name' => 'Tee One',
            'slug' => 'tee-one',
            'price' => 30000,
            'category' => 'T-Shirts',
            'is_active' => true,
        ]);

        WearProduct::create([
            'name' => 'Tee Two',
            'slug' => 'tee-two',
            'price' => 32000,
            'category' => 'T-Shirts',
            'is_active' => true,
        ]);

        WearProduct::create([
            'name' => 'Hidden Polo',
            'slug' => 'hidden-polo',
            'price' => 40000,
            'category' => 'Polos',
            'is_active' => false,
        ]);

        $this->getJson('/api/v1/wear/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'T-Shirts')
            ->assertJsonPath('data.0.slug', 't-shirts');
    }

    public function test_catalog_rejects_invalid_per_page_parameter(): void
    {
        $this->getJson('/api/v1/wear/products?per_page=100')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }
    public function test_collection_endpoints_return_active_collections_and_products(): void
    {
        $collection = \App\Models\Wear\WearCollection::create([
            'name' => 'Streetwear',
            'slug' => 'streetwear',
            'description' => 'Bold styles.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = WearProduct::create([
            'name' => 'Street Tee',
            'slug' => 'street-tee',
            'price' => 45000,
            'category' => 'T-Shirts',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $hidden = WearProduct::create([
            'name' => 'Hidden Street Tee',
            'slug' => 'hidden-street-tee',
            'price' => 45000,
            'category' => 'T-Shirts',
            'is_active' => false,
        ]);

        $collection->products()->attach($product->id, ['sort_order' => 1]);
        $collection->products()->attach($hidden->id, ['sort_order' => 2]);

        $this->getJson('/api/v1/wear/collections')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'streetwear')
            ->assertJsonPath('data.0.product_count', 1);

        $this->getJson('/api/v1/wear/collections/streetwear')
            ->assertOk()
            ->assertJsonPath('data.slug', 'streetwear')
            ->assertJsonPath('data.products.0.slug', 'street-tee')
            ->assertJsonMissingPath('data.products.1');

        $collection->update(['is_active' => false]);

        $this->getJson('/api/v1/wear/collections/streetwear')
            ->assertNotFound();
    }

}
