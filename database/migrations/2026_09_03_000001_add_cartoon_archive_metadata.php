<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cartoons', function (Blueprint $table) {
            $table->text('caption')->nullable()->after('description');
            $table->string('artwork_format', 20)->default('landscape')->after('thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('cartoons', function (Blueprint $table) {
            $table->dropColumn(['caption', 'artwork_format']);
        });
    }
};
