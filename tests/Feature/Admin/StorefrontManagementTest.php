<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Models\Administration\AuditLog;
use App\Models\User;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StorefrontManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_authorized_admin_can_view_and_update_storefront(): void
    {
        $admin = $this->admin();
        $product = WearProduct::factory()->create(['is_active' => true]);
        $collection = WearCollection::factory()->create(['is_active' => true]);

        $this->actingAs($admin)->get(route('admin.storefront.index'))->assertOk()->assertSee('Control what customers see');

        $this->actingAs($admin)->put(route('admin.storefront.update'), [
            'hero_eyebrow' => 'NEW DROP',
            'hero_title' => 'Built for your everyday.',
            'hero_description' => 'Fresh pieces.',
            'hero_cta_label' => 'Explore',
            'hero_cta_url' => '/shop',
            'hero_is_active' => '1',
            'homepage_featured_product_ids' => [$product->id],
            'homepage_featured_collection_ids' => [$collection->id],
            'shop_banner_title' => 'The new collection',
            'shop_banner_description' => 'Shop the latest.',
            'shop_banner_is_active' => '1',
            'shop_default_sort' => 'featured',
        ])->assertRedirect(route('admin.storefront.index'));

        $this->assertDatabaseHas('storefront_settings', ['key' => 'hero_title', 'value' => 'Built for your everyday.']);
        $this->assertDatabaseHas('storefront_settings', ['key' => 'homepage_featured_product_ids', 'value' => json_encode([$product->id])]);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'action' => 'admin.storefront.updated']);
        $this->assertNotNull(AuditLog::where('actor_id', $admin->id)->latest('created_at')->first());
    }

    public function test_non_admin_cannot_manage_storefront(): void
    {
        $user = User::factory()->create(['role' => UserRole::User, 'status' => 'active']);

        $this->actingAs($user)->get(route('admin.storefront.index'))->assertForbidden();
    }
}
