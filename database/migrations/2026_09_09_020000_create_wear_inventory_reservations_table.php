<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('wear_orders', 'checkout_idempotency_key')) {
            Schema::table('wear_orders', function (Blueprint $table): void {
                $table->string('checkout_idempotency_key', 100)->nullable()->unique()->after('order_number');
            });
        }

        Schema::create('wear_stock_reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wear_order_id')->unique()->constrained('wear_orders')->cascadeOnDelete();
            $table->string('status', 20)->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'expires_at']);
        });

        Schema::create('wear_stock_reservation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reservation_id')->constrained('wear_stock_reservations')->cascadeOnDelete();
            $table->foreignId('wear_product_variant_id')->constrained('wear_product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(
                ['reservation_id', 'wear_product_variant_id'],
                'wear_reservation_items_reservation_variant_unique'
            );
            $table->index('wear_product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_stock_reservation_items');
        Schema::dropIfExists('wear_stock_reservations');
        if (Schema::hasColumn('wear_orders', 'checkout_idempotency_key')) {
            Schema::table('wear_orders', function (Blueprint $table): void {
                $table->dropUnique(['checkout_idempotency_key']);
                $table->dropColumn('checkout_idempotency_key');
            });
        }
    }
};
