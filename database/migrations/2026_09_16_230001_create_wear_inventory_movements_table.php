<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The table may already exist if the migration was previously applied manually
        // or a prior run created it before the migration was recorded.
        if (Schema::hasTable('wear_inventory_movements')) {
            return;
        }

        Schema::create('wear_inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wear_product_variant_id')->constrained('wear_product_variants')->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reason', 60);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['wear_product_variant_id', 'created_at'], 'wear_inv_mov_variant_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_inventory_movements');
    }
};
