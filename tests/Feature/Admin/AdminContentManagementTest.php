<?php

namespace Tests\Feature\Admin;

use App\Enums\Auth\UserRole;
use App\Enums\Content\ContentStatus;
use App\Models\User;
use App\Models\Content\Cartoon;
use App\Models\Content\Category;
use App\Models\Content\Collection;
use App\Models\Administration\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_access_admin_api(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_can_access_dashboard_through_permissions(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['users', 'categories', 'cartoons', 'published_cartoons', 'draft_cartoons', 'episodes', 'collections']]);
    }

    public function test_admin_can_create_and_publish_cartoon(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Kids', 'slug' => 'kids', 'is_active' => true]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/cartoons', [
            'category_id' => $category->id,
            'title' => 'Kipanya Adventure',
            'slug' => 'kipanya-adventure',
            'status' => 'draft',
        ]);

        $response->assertCreated()->assertJsonPath('data.status', ContentStatus::Draft->value);
        $id = $response->json('data.id');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/cartoons/{$id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', ContentStatus::Published->value);

        $this->assertDatabaseHas('cartoons', ['id' => $id, 'status' => 'published']);
    }

    public function test_admin_can_manage_categories_and_collections(): void
    {
        $admin = $this->admin();

        $category = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', [
                'name' => 'Family Stories',
                'slug' => 'family-stories',
                'is_active' => true,
            ])
            ->assertCreated()
            ->json('data');

        $cartoon = Cartoon::create([
            'category_id' => $category['id'],
            'title' => 'Collection Story',
            'slug' => 'collection-story',
            'status' => ContentStatus::Draft,
        ]);

        $collection = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/collections', [
                'name' => 'Best Of',
                'slug' => 'best-of',
                'is_active' => true,
            ])
            ->assertCreated()
            ->json('data');

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/collections/{$collection['id']}/cartoons", [
                'cartoon_ids' => [$cartoon->id],
            ])
            ->assertOk();

        $this->assertTrue(Collection::findOrFail($collection['id'])->cartoons()->whereKey($cartoon->id)->exists());
    }
    public function test_admin_mutations_are_audited(): void
    {
        $admin = $this->admin();

        $category = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/categories', [
                'name' => 'Audit Category',
                'slug' => 'audit-category',
                'is_active' => true,
            ])
            ->assertCreated()
            ->json('data');

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.category.created',
            'auditable_type' => Category::class,
            'auditable_id' => $category['id'],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/categories/{$category['id']}")
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'admin.category.deleted',
            'auditable_type' => Category::class,
            'auditable_id' => $category['id'],
        ]);

        $this->assertSame(2, AuditLog::where('actor_id', $admin->id)->count());
    }

}
