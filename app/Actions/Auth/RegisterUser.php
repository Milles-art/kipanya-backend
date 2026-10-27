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
                'email' => $data->email,
            ]);

            // password/device_hash/signup_ip are intentionally not mass
            // assignable; the hashed cast turns the plaintext password into a
            // bcrypt hash here.
            // phone_verified_at keeps its historical meaning of "this number is
            // the account's confirmed contact channel" — no SMS proof exists in
            // the password flow, so it is asserted at sign-up and gates
            // downstream flows (e.g. adding an email in account settings).
            $user->forceFill([
                'phone_verified_at' => now(),
                'status' => UserStatus::Active->value,
                'password' => $data->password,
                'device_hash' => $data->deviceHash,
                'signup_ip' => $data->signupIp,
            ])->save();

            $this->assignRole->execute($user, 'user');

            $user->profile()->create();
            $user->notificationPreferences()->create();

            return $user->fresh();
        });
    }
}
