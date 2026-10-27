<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('gateway_order_id', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropUnique(['gateway_order_id']);
            $table->dropColumn('gateway_order_id');
        });
    }
};
