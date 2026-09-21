<?php

namespace App\Actions\Auth;

use App\Actions\Authorization\AssignRoleToUser;
use App\DTOs\Auth\RegisterUserData;
use App\Enums\Auth\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RegisterUser
{
    public function __construct(private readonly AssignRoleToUser $assignRole) {}

    public function execute(RegisterUserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data->name,
                'phone' => $data->phone,
            ]);

            $user->forceFill([
                'phone_verified_at' => now(),
                'status' => UserStatus::Active->value,
            ])->save();

            $this->assignRole->execute($user, 'user');

            $user->profile()->create();
            $user->notificationPreferences()->create();

            return $user->fresh();
        });
    }
}
