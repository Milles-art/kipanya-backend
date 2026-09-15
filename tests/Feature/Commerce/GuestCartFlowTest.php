<?php

namespace Tests\Feature\Commerce;

use App\Enums\Auth\UserStatus;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCartFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_cart_and_add_variant(): void
    {
        $product = WearProduct::create([
            'name' => 'Test Hoodie', 'slug' => 'test-hoodie', 'price' => 45000,
            'category' => 'hoodies', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 5, 'sku' => 'TEST-HOODIE-M-BLK',
        ]);

        $response = $this->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonStructure(['data' => ['guest_cart_token']]);

        $token = $response->json('data.guest_cart_token');
        $this->assertIsString($token);
        $this->assertSame(64, strlen($token));
        $this->assertDatabaseMissing('carts', ['guest_token_hash' => $token]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_guest_cart_can_be_merged_into_authenticated_cart(): void
    {
        $product = WearProduct::create([
            'name' => 'Test Shirt', 'slug' => 'test-shirt', 'price' => 30000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'L', 'color' => 'White',
            'stock' => 10, 'sku' => 'TEST-SHIRT-L-WHT',
        ]);

        $response = $this->postJson('/api/v1/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);
        $token = $response->json('data.guest_cart_token');

        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cart/merge', ['guest_cart_token' => $token])
            ->assertOk()
            ->assertJsonPath('data.item_count', 3);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('carts', [
            'guest_token_hash' => hash('sha256', $token),
            'status' => 'converted',
        ]);
    }


    public function test_guest_cart_with_frontend_token_length_can_be_merged(): void
    {
        $product = WearProduct::create([
            'name' => 'Frontend Token Tee', 'slug' => 'frontend-token-tee', 'price' => 30000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 10, 'sku' => 'FRONTEND-TOKEN-M-BLK',
        ]);

        $token = bin2hex(random_bytes(32));
        $this->withHeader('X-Guest-Cart-Token', $token)
            ->postJson('/api/v1/cart/items', [
                'variant_id' => $variant->id,
                'quantity' => 2,
            ])
            ->assertOk();

        $user = User::factory()->create([
            'status' => UserStatus::Active,
            'phone_verified_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cart/merge', ['guest_cart_token' => $token])
            ->assertOk()
            ->assertJsonPath('data.item_count', 2);
    }

    public function test_checkout_preview_requires_authentication_and_uses_server_price(): void
    {
        $this->getJson('/api/v1/cart/checkout/preview')->assertUnauthorized();

        $user = User::factory()->create(['status' => UserStatus::Active]);
        $product = WearProduct::create([
            'name' => 'Preview Tee', 'slug' => 'preview-tee', 'price' => 10000,
            'category' => 'shirts', 'is_active' => true,
        ]);
        $variant = WearProductVariant::create([
            'wear_product_id' => $product->id, 'size' => 'M', 'color' => 'Black',
            'stock' => 4, 'sku' => 'PREVIEW-TEE-M-BLK',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/cart/items', ['variant_id' => $variant->id, 'quantity' => 2])
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/cart/checkout/preview')
            ->assertOk()
            ->assertJsonPath('data.subtotal', 20000)
            ->assertJsonPath('data.total', 20000);
    }
}
