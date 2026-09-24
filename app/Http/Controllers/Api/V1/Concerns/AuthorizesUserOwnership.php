<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;

/**
 * Centralised ownership checks for customer-owned resources.
 *
 * Every customer-facing endpoint that addresses a record by its own primary
 * key has to answer one question: "does this row belong to the caller?". When
 * that check is hand-written per action it is easy for a new endpoint to omit
 * it, and an omitted check is an IDOR. Routing every such check through this
 * trait means the rule lives in exactly one place.
 *
 * A 403 is used rather than a 404 so behaviour matches the guards these calls
 * replace, and so the existing cross-tenant regression tests keep asserting
 * the same status code.
 */
trait AuthorizesUserOwnership
{
    /**
     * Abort unless the model belongs to the authenticated user.
     *
     * @param  Model|Relation  $model  A model carrying a `user_id` column, or a
     *                                relation already constrained to the owner.
     */
    protected function assertOwnedBy(Model|Relation $model): void
    {
        $user = request()->user();

        if ($user === null) {
            abort(401);
        }

        if ($model instanceof Relation) {
            abort_unless($model->whereKey($model->getRelated()->getKey())->exists(), 403);

            return;
        }

        abort_unless(
            $model->getAttribute('user_id') === $user->getKey(),
            403,
        );
    }

    /**
     * Abort unless the model belongs to the caller OR the caller holds the
     * given permission. Used by staff-facing reads of customer records.
     */
    protected function assertOwnedByOrCan(Model $model, string $permission): void
    {
        $user = request()->user();

        if ($user === null) {
            abort(401);
        }

        if ($user->hasPermission($permission)) {
            return;
        }

        abort_unless($model->getAttribute('user_id') === $user->getKey(), 403);
    }

    /**
     * Resolve a route-bound model, but only if the caller owns it.
     *
     * Scoping the lookup itself (rather than fetching then comparing) means an
     * unowned id is simply not found, so the existence of another customer's
     * record is never confirmed.
     */
    protected function ownedOrFail(string $modelClass, int|string $key)
    {
        $user = request()->user();

        if ($user === null) {
            abort(401);
        }

        $instance = new $modelClass;

        return $instance->newQuery()
            ->whereKey($key)
            ->where($instance->getForeignKey(), $user->getKey())
            ->firstOrFail();
    }
}
