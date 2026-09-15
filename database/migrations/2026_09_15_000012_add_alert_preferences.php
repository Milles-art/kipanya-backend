<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table): void {
            if (! Schema::hasColumn('notification_preferences', 'restock_enabled')) {
                $table->boolean('restock_enabled')->default(true)->after('marketing_enabled');
            }
            if (! Schema::hasColumn('notification_preferences', 'price_drop_enabled')) {
                $table->boolean('price_drop_enabled')->default(false)->after('restock_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table): void {
            foreach (['price_drop_enabled', 'restock_enabled'] as $column) {
                if (Schema::hasColumn('notification_preferences', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
