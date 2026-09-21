<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wear_product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wear_product_id')->constrained('wear_products')->cascadeOnDelete();
            $table->string('path', 500);
            $table->string('role', 30)->default('gallery')->index();
            $table->string('alt_text', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['wear_product_id', 'path']);
            $table->index(['wear_product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_product_images');
    }
};
