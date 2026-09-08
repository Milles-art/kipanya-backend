<?php

namespace Tests\Feature\Content;

use App\Enums\Content\ContentStatus;
use App\Models\Content\Cartoon;
use App\Models\Content\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_cartoon_is_publicly_visible(): void
    {
        $category = Category::create([
            'name' => 'Kipanya Cartoons',
            'slug' => 'kipanya-cartoons',
            'is_active' => true,
        ]);

        $cartoon = Cartoon::create([
            'category_id' => $category->id,
            'title' => 'Sample Cartoon',
            'slug' => 'sample-cartoon',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/content/cartoons')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'sample-cartoon');

        $this->getJson('/api/v1/content/cartoons/sample-cartoon')
            ->assertOk()
            ->assertJsonPath('data.slug', $cartoon->slug);
    }

    public function test_draft_cartoon_is_not_publicly_visible(): void
    {
        $category = Category::create([
            'name' => 'Drafts',
            'slug' => 'drafts',
            'is_active' => true,
        ]);

        Cartoon::create([
            'category_id' => $category->id,
            'title' => 'Draft Cartoon',
            'slug' => 'draft-cartoon',
            'status' => ContentStatus::Draft,
        ]);

        $this->getJson('/api/v1/content/cartoons')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/v1/content/cartoons/draft-cartoon')
            ->assertNotFound();
    }

    public function test_authenticated_user_can_favorite_and_unfavorite_cartoon(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Favorites',
            'slug' => 'favorites',
            'is_active' => true,
        ]);

        $cartoon = Cartoon::create([
            'category_id' => $category->id,
            'title' => 'Favorite Me',
            'slug' => 'favorite-me',
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/content/cartoons/{$cartoon->id}/favorite")
            ->assertOk();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'cartoon_id' => $cartoon->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/content/cartoons/{$cartoon->id}/favorite")
            ->assertOk();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'cartoon_id' => $cartoon->id,
        ]);
    }
}
