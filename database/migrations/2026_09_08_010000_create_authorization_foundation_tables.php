<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('module', 80)->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        $roles = [
            ['name' => 'User', 'slug' => 'user', 'description' => 'Default Kipanya customer account.', 'is_system' => true],
            ['name' => 'Super Administrator', 'slug' => 'super_admin', 'description' => 'Full platform administration.', 'is_system' => true],
            ['name' => 'Content Manager', 'slug' => 'content_manager', 'description' => 'Manages editorial content.', 'is_system' => false],
            ['name' => 'Commerce Manager', 'slug' => 'commerce_manager', 'description' => 'Manages commerce operations.', 'is_system' => false],
            ['name' => 'Moderator', 'slug' => 'moderator', 'description' => 'Moderates community content.', 'is_system' => false],
            ['name' => 'Support', 'slug' => 'support', 'description' => 'Handles customer support operations.', 'is_system' => false],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $permissions = [
            ['name' => 'View admin dashboard', 'slug' => 'admin.dashboard.view', 'module' => 'admin'],
            ['name' => 'Manage categories', 'slug' => 'content.categories.manage', 'module' => 'content'],
            ['name' => 'Manage cartoons', 'slug' => 'content.cartoons.manage', 'module' => 'content'],
            ['name' => 'Manage episodes', 'slug' => 'content.episodes.manage', 'module' => 'content'],
            ['name' => 'Manage collections', 'slug' => 'content.collections.manage', 'module' => 'content'],
            ['name' => 'Moderate community', 'slug' => 'community.moderate', 'module' => 'community'],
            ['name' => 'Manage commerce', 'slug' => 'commerce.manage', 'module' => 'commerce'],
            ['name' => 'Manage payments', 'slug' => 'payments.manage', 'module' => 'payments'],
            ['name' => 'Manage rewards', 'slug' => 'rewards.manage', 'module' => 'rewards'],
            ['name' => 'View analytics', 'slug' => 'analytics.view', 'module' => 'analytics'],
            ['name' => 'Manage settings', 'slug' => 'settings.manage', 'module' => 'settings'],
            ['name' => 'Manage users', 'slug' => 'users.manage', 'module' => 'users'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $superAdminId = DB::table('roles')->where('slug', 'super_admin')->value('id');
        $userRoleId = DB::table('roles')->where('slug', 'user')->value('id');
        $permissionIds = DB::table('permissions')->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permission')->insertOrIgnore([
                'role_id' => $superAdminId,
                'permission_id' => $permissionId,
            ]);
        }

        DB::table('users')->orderBy('id')->eachById(function ($user) use ($superAdminId, $userRoleId): void {
            DB::table('user_role')->insertOrIgnore([
                'user_id' => $user->id,
                'role_id' => $user->role === 'admin' ? $superAdminId : $userRoleId,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
