<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wear_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cartoon_id')->constrained()->cascadeOnDelete();
            $table->string('color', 30)->default('black');
            $table->string('size', 5)->default('M');
            $table->string('placement', 30)->default('front-center');
            $table->json('configuration')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();
            $table->index(['user_id', 'cartoon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_designs');
    }
};
