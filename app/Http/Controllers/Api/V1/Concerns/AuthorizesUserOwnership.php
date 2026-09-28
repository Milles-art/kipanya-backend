<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Database\Eloquent\Model;

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
     * @param  Model  $model  A model carrying a `user_id` column.
     */
    protected function assertOwnedBy(Model $model): void
    {
        $user = request()->user();

        if ($user === null) {
            abort(401);
        }

        abort_unless($this->ownsRecord($model, $user), 403);
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

        abort_unless($this->ownsRecord($model, $user), 403);
    }

    /**
     * Resolve a route-bound model, but only if the caller owns it.
     *
     * Scoping the lookup itself (rather than fetching then comparing) means an
     * unowned id is simply not found, so the existence of another customer's
     * record is never confirmed.
     */
    protected function ownedOrFail(string $modelClass, int|string $key): Model
    {
        $user = request()->user();

        if ($user === null) {
            abort(401);
        }

        // The owner column is `user_id` on every customer-owned table. (Model::getForeignKey()
        // is NOT that: it names the key other tables use to point AT this model, e.g.
        // `wear_order_id`, which would filter on a column that does not exist.)
        return (new $modelClass)->newQuery()
            ->whereKey($key)
            ->where('user_id', $user->getKey())
            ->firstOrFail();
    }

    /**
     * Compare as integers: some drivers return primary/foreign keys as numeric strings, and a
     * strict === between "5" and 5 would wrongly deny the real owner.
     */
    private function ownsRecord(Model $model, Model $user): bool
    {
        $owner = $model->getAttribute('user_id');

        return $owner !== null && (int) $owner === (int) $user->getKey();
    }
}
