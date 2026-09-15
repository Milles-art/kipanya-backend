<?php

namespace Database\Seeders;

use App\Models\Wear\WearProduct;
use Illuminate\Database\Seeder;

class WearDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Kipanya Wear demo catalogue. Each seeded product has its own supplied image.
        $products = [
            ['name' => 'Classic White Sweatshirt', 'slug' => 'classic-white-sweatshirt', 'category' => 'Long Sleeves', 'price' => 56000, 'badge' => 'New', 'description' => 'A clean white sweatshirt with a simple everyday fit.', 'image_path' => 'assets/wear/catalog/generated/product-01.jpg'],
            ['name' => 'Floral Resort Shirt', 'slug' => 'floral-resort-shirt', 'category' => 'Shirts', 'price' => 45000, 'badge' => 'Featured', 'description' => 'A relaxed floral resort shirt made for easy casual styling.', 'image_path' => 'assets/wear/catalog/generated/product-02.jpg'],
            ['name' => 'Blue Statement Tee', 'slug' => 'blue-statement-tee', 'category' => 'T-Shirts', 'price' => 42000, 'badge' => 'Featured', 'description' => 'A graphic white tee with a bold blue statement design.', 'image_path' => 'assets/wear/catalog/generated/product-03.jpg'],
            ['name' => 'Eagle Graphic Tee', 'slug' => 'eagle-graphic-tee', 'category' => 'T-Shirts', 'price' => 44000, 'badge' => null, 'description' => 'A white graphic tee finished with a striking eagle artwork.', 'image_path' => 'assets/wear/catalog/generated/product-04.jpg'],
            ['name' => 'Rose Graphic Tee', 'slug' => 'rose-graphic-tee', 'category' => 'T-Shirts', 'price' => 40000, 'badge' => null, 'description' => 'A clean white tee with a minimal floral graphic.', 'image_path' => 'assets/wear/catalog/generated/product-05.jpg'],
            ['name' => 'Character Print Tee', 'slug' => 'character-print-tee', 'category' => 'T-Shirts', 'price' => 43000, 'badge' => 'New', 'description' => 'A playful graphic tee with a bold character print.', 'image_path' => 'assets/wear/catalog/generated/product-06.jpg'],
            ['name' => 'Yellow Character Hoodie', 'slug' => 'yellow-character-hoodie', 'category' => 'Hoodies', 'price' => 68000, 'badge' => 'New', 'description' => 'A bright yellow hoodie with a playful character graphic.', 'image_path' => 'assets/wear/catalog/generated/product-07.jpg'],
            ['name' => 'Dice Graphic Tee', 'slug' => 'dice-graphic-tee', 'category' => 'T-Shirts', 'price' => 44000, 'badge' => 'Featured', 'description' => 'A black tee with a colorful dice-inspired graphic.', 'image_path' => 'assets/wear/catalog/generated/product-08.jpg'],
            ['name' => 'Flame Graphic Tee', 'slug' => 'flame-graphic-tee', 'category' => 'T-Shirts', 'price' => 42000, 'badge' => null, 'description' => 'A black graphic tee with a bold flame artwork.', 'image_path' => 'assets/wear/catalog/generated/product-09.jpg'],
            ['name' => 'Essential White Long Sleeve', 'slug' => 'essential-white-long-sleeve', 'category' => 'Long Sleeves', 'price' => 58000, 'badge' => null, 'description' => 'A bright white long-sleeve sweatshirt for everyday layering.', 'image_path' => 'assets/wear/catalog/generated/product-10.jpg'],
            ['name' => 'Sky Blue Hoodie', 'slug' => 'sky-blue-hoodie', 'category' => 'Hoodies', 'price' => 69000, 'badge' => 'Featured', 'description' => 'A soft sky-blue hoodie with a clean relaxed silhouette.', 'image_path' => 'assets/wear/catalog/generated/product-11.jpg'],
            ['name' => 'Sand Essential Hoodie', 'slug' => 'sand-essential-hoodie', 'category' => 'Hoodies', 'price' => 68000, 'badge' => null, 'description' => 'A warm sand-toned hoodie with a timeless everyday fit.', 'image_path' => 'assets/wear/catalog/generated/product-12.jpg'],
            ['name' => 'Classic Yellow Polo', 'slug' => 'classic-yellow-polo', 'category' => 'Polos', 'price' => 46000, 'badge' => 'Best', 'description' => 'A classic yellow polo with contrast collar and sleeve trim.', 'image_path' => 'assets/wear/catalog/generated/product-13.jpg'],
            ['name' => 'Navy Character Polo', 'slug' => 'navy-character-polo', 'category' => 'Polos', 'price' => 47000, 'badge' => null, 'description' => 'A navy polo with a playful character graphic.', 'image_path' => 'assets/wear/catalog/generated/product-14.jpg'],
            ['name' => 'Pink Essential Hoodie', 'slug' => 'pink-essential-hoodie', 'category' => 'Hoodies', 'price' => 67000, 'badge' => 'New', 'description' => 'A soft pink hoodie with a clean minimal finish.', 'image_path' => 'assets/wear/catalog/generated/product-15.jpg'],
            ['name' => 'Two-Tone Graphic Sweatshirt', 'slug' => 'two-tone-graphic-sweatshirt', 'category' => 'Long Sleeves', 'price' => 59000, 'badge' => 'Featured', 'description' => 'A distinctive two-tone sweatshirt with a bold front graphic.', 'image_path' => 'assets/wear/catalog/generated/product-16.jpg'],
        ];

        // Keep the catalogue in sync with the supplied 16-product image set.
        WearProduct::query()
            ->whereNotIn('slug', array_column($products, 'slug'))
            ->update(['is_active' => false]);

        foreach ($products as $index => $product) {
            $record = WearProduct::updateOrCreate(
                ['slug' => $product['slug']],
                $product + [
                    'is_featured' => $index < 8,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );

            foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size) {
                foreach (['black', 'white'] as $color) {
                    $record->variants()->updateOrCreate(
                        ['size' => $size, 'color' => $color],
                        ['stock' => 12, 'sku' => strtoupper('KW-'.$record->id.'-'.$size.'-'.$color)]
                    );
                }
            }
        }
    }
}
