<?php

namespace Tests\Feature\Commerce;

use App\Models\Cart\WishlistItem;
use App\Models\User;
use App\Models\Wear\WearProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WishlistFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_requires_authentication(): void
    {
        $product = WearProduct::create([
            'name' => 'Protected Tee',
            'slug' => 'protected-tee',
            'price' => 40000,
            'category' => 'T-Shirts',
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/wishlist')->assertUnauthorized();
        $this->postJson('/api/v1/wishlist', ['product_id' => $product->id])->assertUnauthorized();
        $this->deleteJson('/api/v1/wishlist/'.$product->id)->assertUnauthorized();
    }

    public function test_authenticated_user_can_add_list_and_remove_wishlist_products(): void
    {
        $user = User::factory()->create();

        $first = WearProduct::create([
            'name' => 'Wishlist Tee',
            'slug' => 'wishlist-tee',
            'price' => 40000,
            'category' => 'T-Shirts',
            'image_path' => 'assets/wear/catalog/generated/product-03.jpg',
            'is_active' => true,
        ]);

        $second = WearProduct::create([
            'name' => 'Wishlist Hoodie',
            'slug' => 'wishlist-hoodie',
            'price' => 68000,
            'category' => 'Hoodies',
            'image_path' => 'assets/wear/catalog/generated/product-07.jpg',
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wishlist', ['product_id' => $first->id])
            ->assertCreated()
            ->assertJsonPath('data.product.id', $first->id)
            ->assertJsonPath('data.product.name', 'Wishlist Tee')
            ->assertJsonPath('data.product.image', $first->image_url);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wishlist', ['product_id' => $second->id])
            ->assertCreated()
            ->assertJsonPath('data.product.id', $second->id)
            ->assertJsonPath('data.product.image', $second->image_url);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wishlist')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Wishlist Tee'])
            ->assertJsonFragment(['name' => 'Wishlist Hoodie']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/wishlist/'.$first->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $user->id,
            'wear_product_id' => $first->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/wishlist')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product.id', $second->id);
    }

    public function test_wishlist_prevents_duplicate_products_for_the_same_user(): void
    {
        $user = User::factory()->create();
        $product = WearProduct::create([
            'name' => 'Duplicate Tee',
            'slug' => 'duplicate-tee',
            'price' => 40000,
            'category' => 'T-Shirts',
            'is_active' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wishlist', ['product_id' => $product->id])
            ->assertCreated();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wishlist', ['product_id' => $product->id])
            ->assertCreated();

        $this->assertSame(
            1,
            WishlistItem::where('user_id', $user->id)
                ->where('wear_product_id', $product->id)
                ->count(),
        );
    }

    public function test_inactive_products_cannot_be_added_to_wishlist(): void
    {
        $user = User::factory()->create();
        $product = WearProduct::create([
            'name' => 'Inactive Tee',
            'slug' => 'inactive-wishlist-tee',
            'price' => 40000,
            'category' => 'T-Shirts',
            'is_active' => false,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/wishlist', ['product_id' => $product->id])
            ->assertNotFound();

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $user->id,
            'wear_product_id' => $product->id,
        ]);
    }
}
