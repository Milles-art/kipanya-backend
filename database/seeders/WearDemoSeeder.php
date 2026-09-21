<?php

namespace Database\Seeders;

use App\Models\Administration\StorefrontSetting;
use App\Models\Wear\WearCollection;
use App\Models\Wear\WearProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WearDemoSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * KP Wear's current development catalog.
         *
         * This seeder intentionally replaces the old demo catalogue with the
         * current 44-image catalog. Production is protected by DatabaseSeeder,
         * which skips demo seeders in production.
         */
        $products = [
            // Hoodies
            ['name' => 'KP Redefined Graphic Hoodie', 'slug' => 'kp-redefined-graphic-hoodie', 'category' => 'Hoodies', 'price' => 68000, 'badge' => 'New', 'description' => 'A signature KP Wear hoodie with the Redefined graphic, made for bold everyday streetwear.', 'image_path' => 'assets/wear/catalog/products/hoodies/kp-wear-redefined-graphic-red-front.webp', 'color' => 'Red', 'key' => 'redefined-graphic-red'],
            ['name' => 'KP Icon Hoodie', 'slug' => 'kp-icon-hoodie', 'category' => 'Hoodies', 'price' => 68000, 'badge' => 'Featured', 'description' => 'A clean navy hoodie built around the KP Wear icon for an understated everyday look.', 'image_path' => 'assets/wear/catalog/products/hoodies/kp-wear-kp-icon-navy-front.webp', 'color' => 'Navy', 'key' => 'kp-icon-navy'],
            ['name' => 'Forest & Cream Panel Hoodie', 'slug' => 'forest-cream-panel-hoodie', 'category' => 'Hoodies', 'price' => 69000, 'badge' => null, 'description' => 'A forest-green and cream color-block hoodie with a bold KP Wear icon.', 'image_path' => 'assets/wear/catalog/products/hoodies/kp-wear-forest-cream-panel-front.webp', 'color' => 'Forest Green', 'key' => 'forest-cream-panel'],
            ['name' => 'Mocha Heritage Hoodie', 'slug' => 'mocha-heritage-hoodie', 'category' => 'Hoodies', 'price' => 69000, 'badge' => null, 'description' => 'A cream and mocha heritage-inspired hoodie with a distinctive KP Wear graphic.', 'image_path' => 'assets/wear/catalog/products/hoodies/kp-wear-mocha-cream-panel-front.webp', 'color' => 'Mocha', 'key' => 'mocha-cream-panel'],
            ['name' => 'Black Gold Statement Hoodie', 'slug' => 'black-gold-statement-hoodie', 'category' => 'Hoodies', 'price' => 72000, 'badge' => 'New', 'description' => 'A black streetwear hoodie accented with strong gold geometric detailing and the KP Wear mark.', 'image_path' => 'assets/wear/catalog/products/hoodies/kp-wear-black-gold-statement-front.webp', 'color' => 'Black / Gold', 'key' => 'black-gold-statement'],
            ['name' => 'Navy Blue Colorblock Hoodie', 'slug' => 'navy-blue-colorblock-hoodie', 'category' => 'Hoodies', 'price' => 72000, 'badge' => null, 'description' => 'A navy, blue and white color-block hoodie with a modern athletic streetwear finish.', 'image_path' => 'assets/wear/catalog/products/hoodies/kp-wear-navy-blue-colorblock-front.webp', 'color' => 'Navy / Blue', 'key' => 'navy-blue-colorblock'],

            // Long Sleeves
            ['name' => 'KP Diagonal Long Sleeve', 'slug' => 'kp-diagonal-long-sleeve', 'category' => 'Long Sleeves', 'price' => 58000, 'badge' => 'New', 'description' => 'A black long-sleeve streetwear top with olive and white diagonal panel details.', 'image_path' => 'assets/wear/catalog/products/long-sleeves/kp-wear-black-olive-diagonal-front.webp', 'color' => 'Black / Olive', 'key' => 'black-olive-diagonal'],
            ['name' => 'KP Geo Long Sleeve', 'slug' => 'kp-geo-long-sleeve', 'category' => 'Long Sleeves', 'price' => 58000, 'badge' => null, 'description' => 'A clean white long-sleeve with black and olive geometric detailing.', 'image_path' => 'assets/wear/catalog/products/long-sleeves/kp-wear-white-black-olive-graphic-front.webp', 'color' => 'White / Black / Olive', 'key' => 'white-black-olive-graphic'],
            ['name' => 'Kilimanjaro Heritage Long Sleeve', 'slug' => 'kilimanjaro-heritage-long-sleeve', 'category' => 'Long Sleeves', 'price' => 62000, 'badge' => 'Featured', 'description' => 'A Tanzania-inspired long sleeve featuring a Kilimanjaro-style mountain landscape.', 'image_path' => 'assets/wear/catalog/products/long-sleeves/kp-wear-kilimanjaro-heritage-sand-front.webp', 'color' => 'Sand / Brown', 'key' => 'kilimanjaro-heritage-sand'],
            ['name' => 'Lion Heritage Long Sleeve', 'slug' => 'lion-heritage-long-sleeve', 'category' => 'Long Sleeves', 'price' => 62000, 'badge' => null, 'description' => 'A black heritage-style long sleeve with a tonal lion graphic and KP Wear branding.', 'image_path' => 'assets/wear/catalog/products/long-sleeves/kp-wear-black-lion-front.webp', 'color' => 'Black / Gold', 'key' => 'black-lion'],

            // Polos
            ['name' => 'KP Diagonal Polo', 'slug' => 'kp-diagonal-polo', 'category' => 'Polos', 'price' => 48000, 'badge' => null, 'description' => 'A refined polo with a sand, black and white diagonal design and KP Wear branding.', 'image_path' => 'assets/wear/catalog/products/polos/kp-wear-sand-black-diagonal-front.webp', 'color' => 'Sand / Black', 'key' => 'sand-black-diagonal'],
            ['name' => 'KP Heritage Polo', 'slug' => 'kp-heritage-polo', 'category' => 'Polos', 'price' => 48000, 'badge' => 'Featured', 'description' => 'A clean sand polo with understated brown KP Wear heritage detailing.', 'image_path' => 'assets/wear/catalog/products/polos/kp-wear-sand-brown-minimal-front.webp', 'color' => 'Sand / Brown', 'key' => 'sand-brown-minimal'],
            ['name' => 'KP Redefined Graffiti Polo', 'slug' => 'kp-redefined-graffiti-polo', 'category' => 'Polos', 'price' => 52000, 'badge' => 'New', 'description' => 'A statement polo combining KP Wear graffiti artwork with red and black print accents.', 'image_path' => 'assets/wear/catalog/products/polos/kp-wear-sand-red-graffiti-front.webp', 'color' => 'Sand / Red / Black', 'key' => 'sand-red-graffiti'],
            ['name' => 'KP Classic Polo Burgundy', 'slug' => 'kp-classic-polo-burgundy', 'category' => 'Polos', 'price' => 47000, 'badge' => null, 'description' => 'A clean burgundy polo finished with KP Wear branding and contrast sleeve details.', 'image_path' => 'assets/wear/catalog/products/polos/kp-wear-burgundy-classic-front.webp', 'color' => 'Burgundy', 'key' => 'burgundy-classic'],
            ['name' => 'KP Icon Polo Black', 'slug' => 'kp-icon-polo-black', 'category' => 'Polos', 'price' => 47000, 'badge' => null, 'description' => 'A timeless black polo featuring the KP Wear icon in a clean, minimal placement.', 'image_path' => 'assets/wear/catalog/products/polos/kp-wear-black-icon-front.webp', 'color' => 'Black', 'key' => 'black-icon'],
            ['name' => 'KP Redefined Graffiti Polo White', 'slug' => 'kp-redefined-graffiti-polo-white', 'category' => 'Polos', 'price' => 52000, 'badge' => 'New', 'description' => 'A white statement polo with bold Redefined graffiti artwork and KP Wear branding.', 'image_path' => 'assets/wear/catalog/products/polos/kp-wear-white-graffiti-front.webp', 'color' => 'White / Red / Black', 'key' => 'white-graffiti'],
            ['name' => 'KP Minimal Icon Polo', 'slug' => 'kp-minimal-icon-polo', 'category' => 'Polos', 'price' => 47000, 'badge' => null, 'description' => 'A minimal sand polo with the KP Wear icon for smart everyday styling.', 'image_path' => 'assets/wear/catalog/products/polos/kp-wear-sand-minimal-icon-front.webp', 'color' => 'Sand', 'key' => 'sand-minimal-icon'],

            // T-Shirts
            ['name' => 'KP Icon T-Shirt Black', 'slug' => 'kp-icon-t-shirt-black', 'category' => 'T-Shirts', 'price' => 42000, 'badge' => 'Featured', 'description' => 'A black KP Wear T-shirt with the signature icon and clean streetwear finish.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-icon-black-front.webp', 'color' => 'Black', 'key' => 'kp-icon-black'],
            ['name' => 'KP Redefined Graffiti T-Shirt White', 'slug' => 'kp-redefined-graffiti-t-shirt-white', 'category' => 'T-Shirts', 'price' => 44000, 'badge' => 'New', 'description' => 'A white graphic tee with bold Redefined graffiti artwork and KP Wear branding.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-redefined-graffiti-white-front.webp', 'color' => 'White', 'key' => 'redefined-graffiti-white'],
            ['name' => 'KP City Crown T-Shirt Sand', 'slug' => 'kp-city-crown-t-shirt-sand', 'category' => 'T-Shirts', 'price' => 45000, 'badge' => null, 'description' => 'A sand T-shirt combining the KP icon with city textures and a red crown motif.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-city-crown-sand-front.webp', 'color' => 'Sand', 'key' => 'city-crown-sand'],
            ['name' => 'Nothing But Konfidence T-Shirt', 'slug' => 'nothing-but-konfidence-t-shirt-black', 'category' => 'T-Shirts', 'price' => 44000, 'badge' => 'Featured', 'description' => 'A black graphic T-shirt built around KP Wear’s Nothing But Konfidence message.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-nothing-but-konfidence-black-front.webp', 'color' => 'Black', 'key' => 'nothing-but-konfidence-black'],
            ['name' => 'KP Signature Block T-Shirt White', 'slug' => 'kp-signature-block-t-shirt-white', 'category' => 'T-Shirts', 'price' => 43000, 'badge' => null, 'description' => 'A white KP signature block T-shirt with a clean graphic layout and everyday fit.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-signature-block-white-front.webp', 'color' => 'White', 'key' => 'kp-signature-block-white'],
            ['name' => 'KP Mascot T-Shirt Black', 'slug' => 'kp-mascot-t-shirt-black', 'category' => 'T-Shirts', 'price' => 42000, 'badge' => null, 'description' => 'A minimalist black KP Wear tee featuring the signature mascot icon.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-mascot-black-front.webp', 'color' => 'Black', 'key' => 'kp-mascot-black'],
            ['name' => 'KP Block T-Shirt Black', 'slug' => 'kp-block-t-shirt-black', 'category' => 'T-Shirts', 'price' => 43000, 'badge' => 'New', 'description' => 'A black KP block T-shirt with a bold logo treatment and clean back finish.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-block-black-front.webp', 'color' => 'Black', 'key' => 'kp-block-black'],
            ['name' => 'Know Your Worth T-Shirt Peach', 'slug' => 'know-your-worth-t-shirt-peach', 'category' => 'T-Shirts', 'price' => 45000, 'badge' => null, 'description' => 'A peach women’s T-shirt carrying the Know Your Worth graphic with a confident streetwear feel.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-know-your-worth-peach-front.webp', 'color' => 'Peach', 'key' => 'know-your-worth-peach'],
            ['name' => 'Real Men Build Better T-Shirt Navy', 'slug' => 'real-men-build-better-t-shirt-navy', 'category' => 'T-Shirts', 'price' => 45000, 'badge' => 'Featured', 'description' => 'A navy statement tee built around the Real Men Build Better message.', 'image_path' => 'assets/wear/catalog/products/t-shirts/kp-wear-real-men-build-better-navy-front.webp', 'color' => 'Navy', 'key' => 'real-men-build-better-navy'],
        ];

        $gallery = [
            'redefined-graphic-red' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-redefined-graphic-red-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-redefined-graphic-red-back.webp'],
                ['role' => 'lifestyle', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-redefined-graphic-red-lifestyle.webp'],
            ],
            'kp-icon-navy' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-kp-icon-navy-front.webp'],
            ],
            'forest-cream-panel' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-forest-cream-panel-front.webp']],
            'mocha-cream-panel' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-mocha-cream-panel-front.webp']],
            'black-gold-statement' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-black-gold-statement-front.webp']],
            'navy-blue-colorblock' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/hoodies/kp-wear-navy-blue-colorblock-front.webp']],
            'black-olive-diagonal' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/long-sleeves/kp-wear-black-olive-diagonal-front.webp']],
            'white-black-olive-graphic' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/long-sleeves/kp-wear-white-black-olive-graphic-front.webp']],
            'kilimanjaro-heritage-sand' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/long-sleeves/kp-wear-kilimanjaro-heritage-sand-front.webp']],
            'black-lion' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/long-sleeves/kp-wear-black-lion-front.webp']],
            'sand-black-diagonal' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/polos/kp-wear-sand-black-diagonal-front.webp']],
            'sand-brown-minimal' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/polos/kp-wear-sand-brown-minimal-front.webp']],
            'sand-red-graffiti' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/polos/kp-wear-sand-red-graffiti-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/polos/kp-wear-sand-red-graffiti-back.webp'],
            ],
            'burgundy-classic' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/polos/kp-wear-burgundy-classic-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/polos/kp-wear-burgundy-classic-back.webp'],
            ],
            'black-icon' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/polos/kp-wear-black-icon-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/polos/kp-wear-black-icon-back.webp'],
            ],
            'white-graffiti' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/polos/kp-wear-white-graffiti-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/polos/kp-wear-white-graffiti-back.webp'],
                ['role' => 'front-detail', 'file' => 'assets/wear/catalog/products/polos/kp-wear-white-graffiti-front-detail.webp'],
                ['role' => 'back-detail', 'file' => 'assets/wear/catalog/products/polos/kp-wear-white-graffiti-back-detail.webp'],
            ],
            'sand-minimal-icon' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/polos/kp-wear-sand-minimal-icon-front.webp']],
            'kp-icon-black' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-icon-black-front.webp']],
            'redefined-graffiti-white' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-redefined-graffiti-white-front.webp']],
            'city-crown-sand' => [['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-city-crown-sand-front.webp']],
            'nothing-but-konfidence-black' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-nothing-but-konfidence-black-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-nothing-but-konfidence-black-back.webp'],
                ['role' => 'lifestyle', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-nothing-but-konfidence-black-lifestyle.webp'],
            ],
            'kp-signature-block-white' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-signature-block-white-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-signature-block-white-back.webp'],
                ['role' => 'lifestyle', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-signature-block-white-lifestyle.webp'],
                ['role' => 'lifestyle-2', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-signature-block-white-lifestyle-2.webp'],
            ],
            'kp-mascot-black' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-mascot-black-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-mascot-black-back.webp'],
            ],
            'kp-block-black' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-block-black-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-block-black-back.webp'],
                ['role' => 'lifestyle', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-kp-block-black-lifestyle.webp'],
            ],
            'know-your-worth-peach' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-know-your-worth-peach-front.webp'],
                ['role' => 'lifestyle', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-know-your-worth-peach-lifestyle.webp'],
            ],
            'real-men-build-better-navy' => [
                ['role' => 'front', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-real-men-build-better-navy-front.webp'],
                ['role' => 'back', 'file' => 'assets/wear/catalog/products/t-shirts/kp-wear-real-men-build-better-navy-back.webp'],
            ],
        ];

        DB::transaction(function () use ($products, $gallery): void {
            // The old catalogue is demo data. Delete products while preserving
            // historical order-item rows via their existing null-on-delete FKs.
            $oldProducts = WearProduct::query()->get();
            foreach ($oldProducts as $product) {
                $product->collections()->detach();
                $product->variants()->delete();
                $product->delete();
            }

            foreach ($products as $index => $definition) {
                $product = WearProduct::create([
                    'name' => $definition['name'],
                    'slug' => $definition['slug'],
                    'category' => $definition['category'],
                    'price' => $definition['price'],
                    'compare_at_price' => null,
                    'image_path' => $definition['image_path'],
                    'badge' => $definition['badge'],
                    'description' => $definition['description'],
                    'is_featured' => false,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]);

                $key = $definition['key'];
                foreach (($gallery[$key] ?? [['role' => 'front', 'file' => $definition['image_path']]]) as $imageIndex => $image) {
                    $product->images()->create([
                        'path' => $image['file'],
                        'role' => $image['role'],
                        'alt_text' => $definition['name'].' — '.str_replace('-', ' ', $image['role']),
                        'sort_order' => $imageIndex + 1,
                    ]);
                }

                foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size) {
                    $product->variants()->create([
                        'size' => $size,
                        'color' => $definition['color'],
                        'stock' => 10,
                        'sku' => 'KP-'.strtoupper(match ($definition['category']) {
                            'Hoodies' => 'H',
                            'Long Sleeves' => 'L',
                            'Polos' => 'P',
                            default => 'T',
                        }).str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT).'-'.strtoupper($size),
                    ]);
                }
            }
        });

        $featuredSlugs = [
            'kp-redefined-graphic-hoodie',
            'kp-icon-hoodie',
            'kilimanjaro-heritage-long-sleeve',
            'kp-redefined-graffiti-polo',
            'kp-redefined-graffiti-polo-white',
            'nothing-but-konfidence-t-shirt-black',
            'kp-signature-block-t-shirt-white',
            'real-men-build-better-t-shirt-navy',
        ];

        WearProduct::query()->update(['is_featured' => false]);
        WearProduct::query()->whereIn('slug', $featuredSlugs)->update(['is_featured' => true]);

        $this->call(WearCollectionSeeder::class);

        $featuredIds = WearProduct::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(8)
            ->pluck('id')
            ->values()
            ->all();

        $collectionIds = WearCollection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->pluck('id')
            ->values()
            ->all();

        StorefrontSetting::updateOrCreate(
            ['key' => 'homepage_featured_product_ids'],
            ['group' => 'homepage', 'value' => json_encode($featuredIds)]
        );

        StorefrontSetting::updateOrCreate(
            ['key' => 'homepage_featured_collection_ids'],
            ['group' => 'homepage', 'value' => json_encode($collectionIds)]
        );
    }
}
