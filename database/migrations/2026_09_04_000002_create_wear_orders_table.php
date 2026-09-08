<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wear_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->text('delivery_address');
            $table->string('delivery_city', 80)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('status', 30)->default('pending_payment')->index();
            $table->string('payment_status', 30)->default('pending')->index();
            $table->string('payment_method', 30)->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('wear_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wear_order_id')->constrained('wear_orders')->cascadeOnDelete();
            $table->foreignId('wear_product_id')->nullable()->constrained('wear_products')->nullOnDelete();
            $table->foreignId('wear_product_variant_id')->nullable()->constrained('wear_product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->string('size', 8)->nullable();
            $table->string('color', 30)->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wear_order_items');
        Schema::dropIfExists('wear_orders');
    }
};
