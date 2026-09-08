<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wear\WearOrder;

final class WearOrderPolicy
{
    public function view(User $user, WearOrder $order): bool
    {
        return $order->user_id === $user->id || $user->hasPermission('commerce.manage');
    }
}
