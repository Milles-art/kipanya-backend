<?php

namespace Database\Seeders;

use App\Models\Wear\WearProduct;
use Illuminate\Database\Seeder;

class WearDemoSeeder extends Seeder
{
    public function run(): void
    {
        // KP Wear demo catalogue paired with the generated Design #2 campaign imagery.
        $products = [
            ['name' => 'Essential KP Tee', 'slug' => 'essential-kp-tee', 'category' => 'T-Shirts', 'price' => 32000, 'badge' => 'Best', 'description' => 'A clean black KP Wear tee with an easy everyday silhouette.', 'image_path' => 'assets/wear/catalog/generated/product-01.jpg'],
            ['name' => 'Clean White KP Tee', 'slug' => 'clean-white-kp-tee', 'category' => 'T-Shirts', 'price' => 32000, 'badge' => null, 'description' => 'A crisp white tee finished with the signature KP Wear mark.', 'image_path' => 'assets/wear/catalog/generated/product-02.jpg'],
            ['name' => 'KP Street Hoodie', 'slug' => 'kp-street-hoodie', 'category' => 'Hoodies', 'price' => 65000, 'badge' => 'Featured', 'description' => 'A black hoodie built for relaxed streetwear layering.', 'image_path' => 'assets/wear/catalog/generated/product-03.jpg'],
            ['name' => 'Sand Logo Hoodie', 'slug' => 'sand-logo-hoodie', 'category' => 'Hoodies', 'price' => 68000, 'badge' => 'New', 'description' => 'A soft sand-toned hoodie with a clean front mark.', 'image_path' => 'assets/wear/catalog/generated/product-04.jpg'],
            ['name' => 'Classic KP Cap', 'slug' => 'classic-kp-cap', 'category' => 'Caps', 'price' => 24000, 'badge' => null, 'description' => 'A classic black cap with the KP signature front mark.', 'image_path' => 'assets/wear/catalog/generated/product-05.jpg'],
            ['name' => 'Sand Dad Cap', 'slug' => 'sand-dad-cap', 'category' => 'Caps', 'price' => 24000, 'badge' => null, 'description' => 'A relaxed sand cap made for understated everyday fits.', 'image_path' => 'assets/wear/catalog/generated/product-06.jpg'],
            ['name' => 'KP Crew Sweatshirt', 'slug' => 'kp-crew-sweatshirt', 'category' => 'Long Sleeves', 'price' => 56000, 'badge' => 'Featured', 'description' => 'A black crew sweatshirt with a minimal KP finish.', 'image_path' => 'assets/wear/catalog/generated/product-07.jpg'],
            ['name' => 'Essential White Sweatshirt', 'slug' => 'essential-white-sweatshirt', 'category' => 'Long Sleeves', 'price' => 58000, 'badge' => null, 'description' => 'A bright, clean sweatshirt for easy layering.', 'image_path' => 'assets/wear/catalog/generated/product-08.jpg'],
            ['name' => 'Cream Essential Tee', 'slug' => 'cream-essential-tee', 'category' => 'T-Shirts', 'price' => 34000, 'badge' => 'New', 'description' => 'A warm cream tee with a subtle KP chest mark.', 'image_path' => 'assets/wear/catalog/generated/product-09.jpg'],
            ['name' => 'Tanzania Built Different Tee', 'slug' => 'tanzania-built-different-tee', 'category' => 'T-Shirts', 'price' => 42000, 'badge' => 'New', 'description' => 'A statement tee celebrating Tanzania and the KP Wear attitude.', 'image_path' => 'assets/wear/catalog/generated/product-10.jpg'],
            ['name' => 'Olive Signature Tee', 'slug' => 'olive-signature-tee', 'category' => 'T-Shirts', 'price' => 36000, 'badge' => null, 'description' => 'A deep olive tee with a restrained signature mark.', 'image_path' => 'assets/wear/catalog/generated/product-11.jpg'],
            ['name' => 'Charcoal Crew', 'slug' => 'charcoal-crew', 'category' => 'Long Sleeves', 'price' => 56000, 'badge' => null, 'description' => 'A charcoal crew sweatshirt designed for everyday rotation.', 'image_path' => 'assets/wear/catalog/generated/product-12.jpg'],
            ['name' => 'White Tree Tee', 'slug' => 'white-tree-tee', 'category' => 'T-Shirts', 'price' => 42000, 'badge' => 'Featured', 'description' => 'A white graphic tee with a culture-inspired artwork treatment.', 'image_path' => 'assets/wear/catalog/generated/product-13.jpg'],
            ['name' => 'KP Culture Tee', 'slug' => 'kp-culture-tee', 'category' => 'T-Shirts', 'price' => 42000, 'badge' => 'Featured', 'description' => 'A black statement tee made to carry the KP culture.', 'image_path' => 'assets/wear/catalog/generated/product-14.jpg'],
        ];

        WearProduct::query()
            ->whereNotIn('slug', array_column($products, 'slug'))
            ->update(['is_active' => false]);

        foreach ($products as $index => $product) {
            $record = WearProduct::updateOrCreate(
                ['slug' => $product['slug']],
                $product + [
                    'is_featured' => $index < 6,
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
