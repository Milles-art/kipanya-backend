<?php

namespace App\Actions\Auth;

use App\Models\User;

final class IssueSanctumToken
{
    public function execute(User $user, string $device = 'client'): string
    {
        return $user->createToken($device)->plainTextToken;
    }
}
