<?php

namespace App\Services\Authorization;

use App\Models\User;

final class AuthorizationService
{
    /**
     * The explicit roles/permissions pivot is the single authoritative source
     * for permission checks. This intentionally delegates to User::hasPermission
     * so the middleware and the model can never diverge.
     */
    public function hasPermission(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }
}
