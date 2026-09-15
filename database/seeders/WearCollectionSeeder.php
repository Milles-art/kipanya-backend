<?php

namespace Database\Seeders;

use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use Illuminate\Database\Seeder;

class WearCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'name' => 'New Arrivals',
                'slug' => 'new-arrivals',
                'description' => 'Fresh drops and new stories. Be the first to wear them.',
                'sort_order' => 1,
                'products' => [
                    'pink-essential-hoodie',
                    'two-tone-graphic-sweatshirt',
                    'navy-character-polo',
                    'classic-yellow-polo',
                    'sky-blue-hoodie',
                    'character-print-tee',
                ],
            ],
            [
                'name' => 'Everyday Essentials',
                'slug' => 'everyday-essentials',
                'description' => 'Timeless pieces made for everyday life.',
                'sort_order' => 2,
                'products' => [
                    'classic-white-sweatshirt',
                    'essential-white-long-sleeve',
                    'sand-essential-hoodie',
                    'sky-blue-hoodie',
                    'classic-yellow-polo',
                    'navy-character-polo',
                ],
            ],
            [
                'name' => 'Streetwear',
                'slug' => 'streetwear',
                'description' => 'Bold styles for a bigger tomorrow.',
                'sort_order' => 3,
                'products' => [
                    'yellow-character-hoodie',
                    'dice-graphic-tee',
                    'flame-graphic-tee',
                    'character-print-tee',
                    'blue-statement-tee',
                    'two-tone-graphic-sweatshirt',
                ],
            ],
            [
                'name' => 'Polos & Shirts',
                'slug' => 'polos-and-shirts',
                'description' => 'Smart looks with everyday confidence.',
                'sort_order' => 4,
                'products' => [
                    'floral-resort-shirt',
                    'classic-yellow-polo',
                    'navy-character-polo',
                ],
            ],
            [
                'name' => 'T-Shirts',
                'slug' => 't-shirts',
                'description' => 'Classic fits. Endless possibilities.',
                'sort_order' => 5,
                'products' => [
                    'blue-statement-tee',
                    'eagle-graphic-tee',
                    'rose-graphic-tee',
                    'character-print-tee',
                    'dice-graphic-tee',
                    'flame-graphic-tee',
                ],
            ],
            [
                'name' => 'Hoodies & Sweatshirts',
                'slug' => 'hoodies-and-sweatshirts',
                'description' => 'Comfort that moves with you.',
                'sort_order' => 6,
                'products' => [
                    'yellow-character-hoodie',
                    'sky-blue-hoodie',
                    'sand-essential-hoodie',
                    'pink-essential-hoodie',
                    'classic-white-sweatshirt',
                    'two-tone-graphic-sweatshirt',
                ],
            ],
            [
                'name' => 'Premium Edit',
                'slug' => 'premium-edit',
                'description' => 'Refined. Elevated. Distinctly Kipanya.',
                'sort_order' => 7,
                'products' => [
                    'two-tone-graphic-sweatshirt',
                    'sand-essential-hoodie',
                    'classic-white-sweatshirt',
                    'floral-resort-shirt',
                ],
            ],
        ];

        foreach ($collections as $definition) {
            $productSlugs = $definition['products'];
            unset($definition['products']);

            $collection = WearCollection::updateOrCreate(
                ['slug' => $definition['slug']],
                $definition + ['is_active' => true]
            );

            $productIds = WearProduct::query()
                ->whereIn('slug', $productSlugs)
                ->pluck('id', 'slug');

            $sync = [];
            foreach ($productSlugs as $index => $slug) {
                if ($productIds->has($slug)) {
                    $sync[$productIds[$slug]] = ['sort_order' => $index + 1];
                }
            }

            $collection->products()->sync($sync);
        }
    }
}
