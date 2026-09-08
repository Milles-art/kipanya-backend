<?php

use App\Enums\Content\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartoons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('status', 20)->default(ContentStatus::Draft->value)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartoons');
    }
};
