<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content\Category;

final class CategoryPolicy
{
    public function manage(User $user, ?Category $category = null): bool { return $user->hasPermission('content.categories.manage'); }
}
