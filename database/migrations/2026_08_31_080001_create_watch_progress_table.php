<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('watch_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cartoon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('episode_id')->constrained('cartoon_episodes')->cascadeOnDelete();
            $table->unsignedInteger('progress_seconds')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_watched_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['user_id', 'episode_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('watch_progress'); }
};
