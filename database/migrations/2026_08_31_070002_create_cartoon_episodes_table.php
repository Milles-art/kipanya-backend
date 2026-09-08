<?php

use App\Enums\Content\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartoon_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cartoon_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200);
            $table->text('description')->nullable();
            $table->string('youtube_video_id', 50)->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('episode_number');
            $table->string('status', 20)->default(ContentStatus::Draft->value)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['cartoon_id', 'episode_number']);
            $table->unique(['cartoon_id', 'slug']);
            $table->index(['cartoon_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartoon_episodes');
    }
};
