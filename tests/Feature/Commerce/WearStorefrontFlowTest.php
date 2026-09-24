<?php

namespace Tests\Feature\Commerce;

use App\Models\Administration\StorefrontSetting;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WearStorefrontFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_returns_renderable_featured_product_and_collection_images(): void
    {
        $product = WearProduct::factory()->create([
            'is_active' => true,
            'image_path' => 'assets/wear/catalog/generated/product-04.jpg',
        ]);
        WearProductVariant::factory()->create([
            'wear_product_id' => $product->id,
            'stock' => 5,
        ]);

        $collection = WearCollection::factory()->create([
            'is_active' => true,
            'cover_path' => 'assets/wear/collections/hero/hero.jpg',
        ]);

        StorefrontSetting::query()->updateOrCreate(
            ['key' => 'homepage_featured_product_ids'],
            ['group' => 'homepage', 'value' => json_encode([$product->id])],
        );
        StorefrontSetting::query()->updateOrCreate(
            ['key' => 'homepage_featured_collection_ids'],
            ['group' => 'homepage', 'value' => json_encode([$collection->id])],
        );

        $response = $this->getJson('/api/v1/wear/storefront')->assertOk();
        $data = $response->json('data.homepage');

        $this->assertSame($product->id, $data['featured_products'][0]['id']);
        $this->assertStringContainsString('/assets/wear/catalog/generated/product-04.jpg', $data['featured_products'][0]['image']);
        $this->assertSame($collection->id, $data['featured_collections'][0]['id']);
        $this->assertStringContainsString('/assets/wear/collections/hero/hero.jpg', $data['featured_collections'][0]['cover']);
    }
    public function test_public_homepage_and_categories_are_image_backed_without_admin_selection(): void
    {
        $hoodie = WearProduct::factory()->create([
            'category' => 'Hoodies',
            'is_active' => true,
            'image_path' => 'assets/wear/catalog/generated/product-11.jpg',
        ]);

        WearProduct::factory()->create([
            'category' => 'T-Shirts',
            'is_active' => true,
            'image_path' => 'assets/wear/catalog/generated/product-03.jpg',
        ]);

        $this->getJson('/api/v1/wear/storefront')
            ->assertOk()
            ->assertJsonPath('data.homepage.featured_collections.0.cover', asset('assets/wear/catalog/generated/product-11.jpg'));

        $this->getJson('/api/v1/wear/categories')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('data.0.image', asset('assets/wear/catalog/generated/product-11.jpg'))
            ->assertJsonPath('data.2.image', asset('assets/wear/catalog/generated/product-03.jpg'));
    }

}
