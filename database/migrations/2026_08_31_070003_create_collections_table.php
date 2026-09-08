<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('cartoon_collection', function (Blueprint $table) {
            $table->foreignId('cartoon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['cartoon_id', 'collection_id']);
            $table->index(['collection_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartoon_collection');
        Schema::dropIfExists('collections');
    }
};
