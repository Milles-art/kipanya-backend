<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wear_orders', function (Blueprint $table): void {
            $table->string('delivery_provider', 100)->nullable()->after('delivery_city');
            $table->string('tracking_number', 120)->nullable()->after('delivery_provider');
            $table->text('fulfillment_notes')->nullable()->after('tracking_number');
            $table->timestamp('shipped_at')->nullable()->after('fulfillment_notes');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->index('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('wear_orders', function (Blueprint $table): void {
            $table->dropIndex(['tracking_number']);
            $table->dropColumn([
                'delivery_provider',
                'tracking_number',
                'fulfillment_notes',
                'shipped_at',
                'delivered_at',
            ]);
        });
    }
};
