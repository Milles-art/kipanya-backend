<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // The secret is stored through Laravel's `encrypted` cast, so the
            // ciphertext needs far more room than a VARCHAR(255) in MySQL.
            $table->text('two_factor_secret')->nullable()->after('onboarding_completed_at');
            $table->timestamp('two_factor_enabled_at')->nullable()->after('two_factor_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['two_factor_secret', 'two_factor_enabled_at']);
        });
    }
};
