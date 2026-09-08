<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content\Cartoon;

final class CartoonPolicy
{
    public function viewAny(User $user): bool { return $user->hasPermission('content.cartoons.manage'); }
    public function view(User $user, Cartoon $cartoon): bool { return $user->hasPermission('content.cartoons.manage'); }
    public function create(User $user): bool { return $user->hasPermission('content.cartoons.manage'); }
    public function update(User $user, Cartoon $cartoon): bool { return $user->hasPermission('content.cartoons.manage'); }
    public function delete(User $user, Cartoon $cartoon): bool { return $user->hasPermission('content.cartoons.manage'); }
    public function manage(User $user, Cartoon $cartoon): bool { return $user->hasPermission('content.cartoons.manage'); }
}
