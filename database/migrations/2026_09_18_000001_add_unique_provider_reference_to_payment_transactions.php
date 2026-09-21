<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A provider reference identifies a single transaction at the payment
 * provider. Enforcing uniqueness (NULLs remain allowed) prevents a retry or
 * race from recording two payment rows that point at the same provider charge.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->dropIndex(['provider_reference']);
            $table->unique('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->dropUnique(['provider_reference']);
            $table->index('provider_reference');
        });
    }
};
