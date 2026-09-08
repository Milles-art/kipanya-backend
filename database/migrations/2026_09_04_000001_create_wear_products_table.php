<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wear_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category', 40)->index();
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('badge', 30)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('wear_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wear_product_id')->constrained('wear_products')->cascadeOnDelete();
            $table->string('size', 8);
            $table->string('color', 30);
            $table->unsignedInteger('stock')->default(0);
            $table->string('sku', 80)->unique();
            $table->timestamps();
            $table->unique(['wear_product_id', 'size', 'color']);
            $table->index(['color', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_product_variants');
        Schema::dropIfExists('wear_products');
    }
};
