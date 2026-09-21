<?php

namespace Database\Seeders;

use App\Enums\Auth\UserRole;
use App\Models\Administration\Role;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $phone = (string) env('ADMIN_PHONE', '');
        $email = (string) env('ADMIN_EMAIL', '');
        $name = (string) env('ADMIN_NAME', 'KP Wear Administrator');

        if ($phone === '' || $email === '') {
            $this->command?->error('Set ADMIN_PHONE and ADMIN_EMAIL in .env before running AdminUserSeeder.');

            return;
        }

        try {
            $phone = PhoneNumber::normalize($phone)->value();
        } catch (\Throwable $e) {
            $this->command?->error('ADMIN_PHONE must be a valid Tanzanian phone number.');

            return;
        }

        $user = User::query()->updateOrCreate(
            ['phone' => $phone],
            [
                'name' => $name,
                'email' => $email,
                'phone_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ],
        );

        $user->forceFill([
            'status' => 'active',
            'role' => UserRole::Admin,
        ])->save();

        $superAdmin = Role::query()->where('slug', 'super_admin')->first();

        if ($superAdmin) {
            $user->roles()->syncWithoutDetaching([$superAdmin->id]);
        }

        $this->command?->info("Admin user ready: {$user->phone}");
    }
}
