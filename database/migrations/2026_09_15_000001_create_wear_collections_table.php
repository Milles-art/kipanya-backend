<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wear_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->string('cover_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('wear_collection_product', function (Blueprint $table) {
            $table->foreignId('wear_collection_id')->constrained('wear_collections')->cascadeOnDelete();
            $table->foreignId('wear_product_id')->constrained('wear_products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['wear_collection_id', 'wear_product_id']);
            $table->index(['wear_collection_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_collection_product');
        Schema::dropIfExists('wear_collections');
    }
};
