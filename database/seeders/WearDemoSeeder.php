<?php

namespace Database\Seeders;

use App\Models\Wear\WearProduct;
use Illuminate\Database\Seeder;

class WearDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Product photos supplied for the Kipanya Wear presentation, stored locally for a reliable demo.
        $products = [
            ['name' => 'Pink Polo Shirt', 'slug' => 'pink-polo-shirt', 'category' => 'Polos', 'price' => 42000, 'badge' => 'New', 'description' => 'A soft pink polo with a smart button collar for an easy, polished everyday look.', 'image_path' => 'assets/wear/catalog/pink-polo.jpg'],
            ['name' => 'Palm Print Resort Shirt', 'slug' => 'palm-print-resort-shirt', 'category' => 'Shirts', 'price' => 45000, 'badge' => 'Featured', 'description' => 'A black resort shirt with a bold palm-panel detail for relaxed weekend styling.', 'image_path' => 'assets/wear/catalog/palm-resort-shirt.jpg'],
            ['name' => 'Maroon Performance Long Sleeve', 'slug' => 'maroon-performance-long-sleeve', 'category' => 'Long Sleeves', 'price' => 48000, 'badge' => null, 'description' => 'A close-fitting maroon long sleeve designed for active days and layered fits.', 'image_path' => 'assets/wear/catalog/maroon-performance-long-sleeve.jpg'],
            ['name' => 'Grey Ringer Tee', 'slug' => 'grey-ringer-tee', 'category' => 'T-Shirts', 'price' => 35000, 'badge' => 'New', 'description' => 'A lightweight grey ringer tee with contrast trim and a clean everyday silhouette.', 'image_path' => 'assets/wear/catalog/grey-ringer-tee.jpg'],
            ['name' => 'Heritage Border Tee', 'slug' => 'heritage-border-tee', 'category' => 'T-Shirts', 'price' => 47000, 'badge' => null, 'description' => 'A black tee with a rich heritage-inspired chest border that makes the outfit.', 'image_path' => 'assets/wear/catalog/heritage-border-tee.jpg'],
            ['name' => 'Essential Black Tee', 'slug' => 'essential-black-tee', 'category' => 'T-Shirts', 'price' => 32000, 'badge' => 'Best', 'description' => 'A clean black crew-neck tee: versatile, comfortable and ready for every day.', 'image_path' => 'assets/wear/catalog/essential-black-tee.jpg'],
            ['name' => 'Sage Polo Shirt', 'slug' => 'sage-polo-shirt', 'category' => 'Polos', 'price' => 40000, 'badge' => 'New', 'description' => 'A textured sage polo that brings a calm colour and refined finish to casual wear.', 'image_path' => 'assets/wear/catalog/sage-polo.jpg'],
            ['name' => 'Sky Textured Tee', 'slug' => 'sky-textured-tee', 'category' => 'T-Shirts', 'price' => 39000, 'badge' => null, 'description' => 'A sky-blue textured tee with a relaxed fit and soft visual detail.', 'image_path' => 'assets/wear/catalog/sky-textured-tee.jpg'],
            ['name' => 'Signature Black Tee', 'slug' => 'signature-black-tee', 'category' => 'T-Shirts', 'price' => 36000, 'badge' => 'Best', 'description' => 'A minimalist black tee finished with a subtle signature mark on the chest.', 'image_path' => 'assets/wear/catalog/signature-black-tee.jpg'],
            ['name' => 'Midnight Graphic Tee', 'slug' => 'midnight-graphic-tee', 'category' => 'T-Shirts', 'price' => 46000, 'badge' => 'Featured', 'description' => 'A midnight tee with a tonal front graphic for understated statement styling.', 'image_path' => 'assets/wear/catalog/midnight-graphic-tee.jpg'],
            ['name' => 'Cream Stripe Tee', 'slug' => 'cream-stripe-tee', 'category' => 'T-Shirts', 'price' => 38000, 'badge' => null, 'description' => 'A cream tee with fine horizontal stripes for a fresh, effortless finish.', 'image_path' => 'assets/wear/catalog/cream-stripe-tee.jpg'],
            ['name' => 'Brown Monogram Tee', 'slug' => 'brown-monogram-tee', 'category' => 'T-Shirts', 'price' => 48000, 'badge' => 'New', 'description' => 'A rich brown tee with a bold monogram detail to anchor a streetwear look.', 'image_path' => 'assets/wear/catalog/brown-monogram-tee.jpg'],
            ['name' => 'Green Mark Tee', 'slug' => 'green-mark-tee', 'category' => 'T-Shirts', 'price' => 43000, 'badge' => null, 'description' => 'A deep green tee with a graphic chest mark for a confident everyday option.', 'image_path' => 'assets/wear/catalog/green-mark-tee.jpg'],
            ['name' => 'Black Oversize Tee', 'slug' => 'black-oversize-tee', 'category' => 'T-Shirts', 'price' => 44000, 'badge' => 'Best', 'description' => 'A roomy black tee with a modern oversized cut for a laid-back streetwear fit.', 'image_path' => 'assets/wear/catalog/black-oversize-tee.jpg'],
        ];

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

            $variantSizes = ['S', 'M', 'L', 'XL', 'XXL'];

            $variantColors = ['black', 'white', 'navy'];

            foreach ($variantSizes as $size) {
                foreach ($variantColors as $color) {
                    $record->variants()->updateOrCreate(
                        ['size' => $size, 'color' => $color],
                        ['stock' => 12, 'sku' => strtoupper('KW-'.$record->id.'-'.$size.'-'.$color)]
                    );
                }
            }
        }
    }
}
