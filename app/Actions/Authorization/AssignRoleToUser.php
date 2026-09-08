<?php

namespace App\Actions\Authorization;

use App\Models\Administration\Role;
use App\Models\User;
use InvalidArgumentException;

final class AssignRoleToUser
{
    public function execute(User $user, string $roleSlug): void
    {
        $role = Role::query()->where('slug', $roleSlug)->first();
        if (! $role) {
            throw new InvalidArgumentException("Unknown role [{$roleSlug}].");
        }
        $user->roles()->syncWithoutDetaching([$role->id]);
    }
}
