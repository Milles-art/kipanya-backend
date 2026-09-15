<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\User;
use App\Models\Wear\WearProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WearCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_view_wear_categories_with_product_counts(): void
    {
        $admin = $this->admin();

        WearProduct::create([
            'name' => 'Hoodie One',
            'slug' => 'hoodie-one',
            'category' => 'Hoodies',
            'price' => 70000,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        WearProduct::create([
            'name' => 'Hoodie Two',
            'slug' => 'hoodie-two',
            'category' => 'Hoodies',
            'price' => 75000,
            'is_active' => false,
            'is_featured' => false,
            'sort_order' => 2,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.wear.categories.index'))
            ->assertOk()
            ->assertSee('Hoodies')
            ->assertSee('Long Sleeves')
            ->assertSee('T-Shirts')
            ->assertSee('Shirts')
            ->assertSee('Polos')
            ->assertSee('2 products')
            ->assertSee('1 active');
    }

    public function test_non_admin_cannot_view_wear_categories(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::User,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('admin.wear.categories.index'))
            ->assertForbidden();
    }
}
