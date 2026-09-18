<?php

namespace App\Actions\Auth;

use App\Models\User;

final class IssueSanctumToken
{
    /**
     * The abilities granted to browser/web-client tokens. Kept as narrow as
     * the storefront needs so a leaked token cannot, say, reach an unrelated
     * future scope; a wildcard `['*']` token is never created by this action.
     */
    public const WEB_ABILITIES = ['auth', 'account', 'commerce', 'cart'];

    public function execute(User $user, string $device = 'web-client', array $abilities = self::WEB_ABILITIES): string
    {
        return $user->createToken($device, $abilities)->plainTextToken;
    }
}
