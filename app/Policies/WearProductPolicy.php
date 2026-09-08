<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wear\WearProduct;

final class WearProductPolicy
{
    public function manage(User $user, ?WearProduct $product = null): bool { return $user->hasPermission('commerce.manage'); }
}
