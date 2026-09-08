<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('description');
            $table->string('application', 50)->default('Platform');
            $table->nullableMorphs('subject');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            $table->index(['application', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('activity_logs'); }
};
