<?php

use App\Models\Content\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        $categories = [
            ['name' => 'Everyday Life', 'description' => 'Relatable moments, routines and everyday stories.'],
            ['name' => 'Humor', 'description' => 'Funny moments, jokes and playful observations.'],
            ['name' => 'Society', 'description' => 'Stories about people, communities and the world around us.'],
            ['name' => 'Family', 'description' => 'Family life, relationships and memorable moments.'],
            ['name' => 'Culture', 'description' => 'Tanzanian and African culture, traditions and identity.'],
            ['name' => 'Work & Business', 'description' => 'Workplace, business and entrepreneurial stories.'],
        ];

        foreach ($categories as $index => $category) {
            Category::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category + ['is_active' => true, 'sort_order' => $index]
            );
        }
    }

    public function down(): void
    {
        // Seed migration intentionally does not remove administrator-created categories.
    }
};
