<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('id');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('status', 20)->default('active')->index()->after('phone_verified_at');
            $table->timestamp('onboarding_completed_at')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'status',
                'onboarding_completed_at',
            ]);
        });
    }
};
