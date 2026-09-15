<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wear_return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wear_order_id')->constrained('wear_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('request_type', 20);
            $table->string('reason', 40);
            $table->json('order_item_ids');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('under_review')->index();
            $table->timestamps();
            $table->index(['user_id', 'wear_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_return_requests');
    }
};
