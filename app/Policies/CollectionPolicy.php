<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content\Collection;

final class CollectionPolicy
{
    public function manage(User $user, ?Collection $collection = null): bool { return $user->hasPermission('content.collections.manage'); }
}
