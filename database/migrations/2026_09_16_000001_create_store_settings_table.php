<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('group', 50)->index();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('store_settings')->insert([
            ['key' => 'store_name', 'group' => 'store', 'value' => 'Kipanya Wear', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'store_tagline', 'group' => 'store', 'value' => 'Everyday wear, made for you.', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'support_email', 'group' => 'store', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'support_phone', 'group' => 'store', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'currency', 'group' => 'commerce', 'value' => 'TZS', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'timezone', 'group' => 'commerce', 'value' => 'Africa/Dar_es_Salaam', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
