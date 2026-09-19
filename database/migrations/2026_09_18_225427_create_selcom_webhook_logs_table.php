<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selcom_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transid', 100)->index();
            $table->string('order_id', 50)->index();
            $table->foreignId('payment_transaction_id')->constrained('payment_transactions')->cascadeOnDelete();
            $table->string('payment_status', 30);
            $table->boolean('processed_successfully')->default(false);
            $table->text('error')->nullable();
            $table->json('raw_payload');
            $table->timestamps();

            $table->unique('transid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('selcom_webhook_logs');
    }
};
