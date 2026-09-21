<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wear_orders', function (Blueprint $table) {
            // sha256 of the checkout request; lets an idempotency key detect a replay
            // that carries a different address/notes.
            $table->string('checkout_fingerprint', 64)->nullable()->after('checkout_idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('wear_orders', function (Blueprint $table) {
            $table->dropColumn('checkout_fingerprint');
        });
    }
};
