<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('group', 50)->index();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['key' => 'hero_eyebrow', 'group' => 'hero', 'value' => 'KIPANYA WEAR'],
            ['key' => 'hero_title', 'group' => 'hero', 'value' => 'Wear your story.'],
            ['key' => 'hero_description', 'group' => 'hero', 'value' => 'Everyday pieces made to feel like you.'],
            ['key' => 'hero_cta_label', 'group' => 'hero', 'value' => 'Shop now'],
            ['key' => 'hero_cta_url', 'group' => 'hero', 'value' => '/shop'],
            ['key' => 'hero_image_desktop', 'group' => 'hero', 'value' => null],
            ['key' => 'hero_image_mobile', 'group' => 'hero', 'value' => null],
            ['key' => 'hero_is_active', 'group' => 'hero', 'value' => '1'],
            ['key' => 'homepage_featured_product_ids', 'group' => 'homepage', 'value' => '[]'],
            ['key' => 'homepage_featured_collection_ids', 'group' => 'homepage', 'value' => '[]'],
            ['key' => 'shop_banner_title', 'group' => 'shop', 'value' => null],
            ['key' => 'shop_banner_description', 'group' => 'shop', 'value' => null],
            ['key' => 'shop_banner_image', 'group' => 'shop', 'value' => null],
            ['key' => 'shop_banner_is_active', 'group' => 'shop', 'value' => '0'],
            ['key' => 'shop_default_sort', 'group' => 'shop', 'value' => 'featured'],
        ];

        DB::table('storefront_settings')->insert(array_map(
            static fn (array $row): array => [...$row, 'created_at' => $now, 'updated_at' => $now],
            $defaults,
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_settings');
    }
};
