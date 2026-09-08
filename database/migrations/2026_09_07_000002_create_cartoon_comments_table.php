<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('cartoon_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cartoon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('cartoon_comments')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['cartoon_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('cartoon_comments'); }
};
