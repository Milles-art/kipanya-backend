<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('username', 60)->nullable()->unique();
            $table->string('avatar_path')->nullable();
            $table->json('interests')->nullable();
            $table->string('locale', 10)->default('en');
            $table->string('timezone', 64)->default('Africa/Dar_es_Salaam');
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('push_enabled')->default(true);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('marketing_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->nullableMorphs('auditable');
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->uuid('request_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['actor_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('user_profiles');
    }
};
