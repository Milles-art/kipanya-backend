<?php

namespace App\Services\Authorization;

use App\Models\User;

final class AuthorizationService
{
    public function hasPermission(User $user, string $permission): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->roles()
            ->whereHas('permissions', static fn ($query) => $query->where('slug', $permission))
            ->exists();
    }
}
