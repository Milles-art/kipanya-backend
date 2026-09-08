<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('cartoons', fn (Blueprint $table) => $table->unsignedBigInteger('shares_count')->default(0)->after('sort_order')); }
    public function down(): void { Schema::table('cartoons', fn (Blueprint $table) => $table->dropColumn('shares_count')); }
};
