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
                'description' => 'Fresh KP Wear drops across hoodies, long sleeves, polos and T-shirts.',
                'sort_order' => 1,
                'products' => [
                    'kp-redefined-graphic-hoodie',
                    'kilimanjaro-heritage-long-sleeve',
                    'kp-redefined-graffiti-polo',
                    'kp-redefined-graffiti-t-shirt-white',
                    'black-gold-statement-hoodie',
                    'know-your-worth-t-shirt-peach',
                ],
            ],
            [
                'name' => 'Everyday Essentials',
                'slug' => 'everyday-essentials',
                'description' => 'Clean, easy-to-style KP Wear pieces for everyday outfits.',
                'sort_order' => 2,
                'products' => [
                    'kp-icon-hoodie',
                    'kp-mascot-t-shirt-black',
                    'kp-minimal-icon-polo',
                    'kp-icon-polo-black',
                    'kp-icon-t-shirt-black',
                ],
            ],
            [
                'name' => 'Streetwear',
                'slug' => 'streetwear',
                'description' => 'Bold graphics, color blocks and statement pieces.',
                'sort_order' => 3,
                'products' => [
                    'nothing-but-konfidence-t-shirt-black',
                    'kp-redefined-graffiti-t-shirt-white',
                    'kp-redefined-graffiti-polo-white',
                    'black-gold-statement-hoodie',
                    'navy-blue-colorblock-hoodie',
                    'kp-diagonal-long-sleeve',
                ],
            ],
            [
                'name' => 'T-Shirts',
                'slug' => 't-shirts',
                'description' => 'KP Wear graphic and everyday T-shirt collection.',
                'sort_order' => 4,
                'products' => [
                    'kp-icon-t-shirt-black',
                    'kp-redefined-graffiti-t-shirt-white',
                    'kp-city-crown-t-shirt-sand',
                    'nothing-but-konfidence-t-shirt-black',
                    'kp-signature-block-t-shirt-white',
                    'kp-mascot-t-shirt-black',
                    'kp-block-t-shirt-black',
                    'know-your-worth-t-shirt-peach',
                    'real-men-build-better-t-shirt-navy',
                ],
            ],
            [
                'name' => 'Hoodies',
                'slug' => 'hoodies',
                'description' => 'KP Wear hoodies in signature and color-block styles.',
                'sort_order' => 5,
                'products' => [
                    'kp-redefined-graphic-hoodie',
                    'kp-icon-hoodie',
                    'forest-cream-panel-hoodie',
                    'mocha-heritage-hoodie',
                    'black-gold-statement-hoodie',
                    'navy-blue-colorblock-hoodie',
                ],
            ],
            [
                'name' => 'Long Sleeves',
                'slug' => 'long-sleeves',
                'description' => 'Long-sleeve graphics inspired by KP Wear street and heritage styling.',
                'sort_order' => 6,
                'products' => [
                    'kp-diagonal-long-sleeve',
                    'kp-geo-long-sleeve',
                    'kilimanjaro-heritage-long-sleeve',
                    'lion-heritage-long-sleeve',
                ],
            ],
            [
                'name' => 'Polos',
                'slug' => 'polos',
                'description' => 'Clean and statement polo designs from KP Wear.',
                'sort_order' => 7,
                'products' => [
                    'kp-diagonal-polo',
                    'kp-heritage-polo',
                    'kp-redefined-graffiti-polo',
                    'kp-classic-polo-burgundy',
                    'kp-icon-polo-black',
                    'kp-redefined-graffiti-polo-white',
                    'kp-minimal-icon-polo',
                ],
            ],
        ];

        $activeSlugs = array_column($collections, 'slug');
        WearCollection::query()
            ->whereNotIn('slug', $activeSlugs)
            ->update(['is_active' => false]);

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
