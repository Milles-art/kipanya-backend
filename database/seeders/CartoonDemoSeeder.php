<?php

namespace Database\Seeders;

use App\Enums\Content\ContentStatus;
use App\Models\Content\Cartoon;
use App\Models\Content\Category;
use App\Models\Content\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CartoonDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categoryNames = ['Everyday Life', 'Humor', 'Society', 'Family', 'Culture', 'Work & Business'];
        $categories = collect($categoryNames)->mapWithKeys(function (string $name, int $index) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true, 'sort_order' => $index + 1]
            );
            return [$name => $category];
        });

        $stories = [
            ['Niteta Maendeleo', 'Society'],
            ['News & Kilimo', 'Work & Business'],
            ['The Archive Character', 'Culture'],
            ['WHO Classroom', 'Society'],
            ['Rich Yet', 'Society'],
            ['Changing the Mind', 'Humor'],
            ['The 2030 Chair', 'Society'],
            ['Maji Safi na Salama', 'Everyday Life'],
            ['Mwenewe Anasemaje?', 'Humor'],
            ['Milioni Kumi', 'Work & Business'],
            ['Safari ya Mwisho', 'Everyday Life'],
            ['Kodi ya Serikali', 'Society'],
            ['Kupika kwa Umma', 'Family'],
            ['Simama Ura...', 'Work & Business'],
            ['Miu Ngano', 'Everyday Life'],
            ['Mfuko wa Miradi', 'Work & Business'],
            ['Nataka Nimtoe Mvunde', 'Humor'],
            ['Spelin Ndo Bingwa', 'Humor'],
            ['Tujenge Kijijini', 'Culture'],
        ];

        $created = [];
        foreach ($stories as $index => [$title, $categoryName]) {
            $slug = 'demo-'.Str::slug($title).'-'.($index + 1);
            $created[] = Cartoon::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categories[$categoryName]->id,
                    'title' => $title,
                    'description' => 'Reference artwork from the Kipanya Cartoon Archive visual set.',
                    'caption' => 'A visual story from the Cartoon Archive.',
                    'thumbnail_url' => '/assets/cartoon/demo/cartoon-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'.jpg',
                    'artwork_format' => $this->detectFormat('/assets/cartoon/demo/cartoon-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'.jpg'),
                    'status' => ContentStatus::Published,
                    'is_featured' => $index === 0,
                    'is_daily' => $index < 10,
                    'daily_date' => $index < 10 ? today()->subDays($index) : null,
                    'published_at' => now()->subDays($index),
                    'sort_order' => $index + 1,
                ]
            );
        }

        $collection = Collection::updateOrCreate(
            ['slug' => 'cartoon-archive-reference-set'],
            [
                'name' => 'Archive Reference Set',
                'description' => 'A visual set used to tune the Cartoon Archive experience around real artwork.',
                'cover_url' => '/assets/cartoon/demo/cartoon-01.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $collection->cartoons()->sync(collect($created)->mapWithKeys(fn (Cartoon $cartoon, int $index) => [$cartoon->id => ['sort_order' => $index + 1]])->all());
    }
    private function detectFormat(string $path): string
    {
        $size = @getimagesize(public_path($path));
        if (!$size || !$size[0] || !$size[1]) return 'landscape';
        $ratio = $size[0] / $size[1];
        return $ratio > 1.15 ? 'landscape' : ($ratio < 0.85 ? 'portrait' : 'square');
    }

}
