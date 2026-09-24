<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Http\Controllers\Controller;
use App\Models\Commerce\LoyaltyAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LoyaltyController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $account = LoyaltyAccount::query()->firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'data' => [
                'points' => (int) $account->points,
                'lifetime_points' => (int) $account->lifetime_points,
                'transactions' => $account->transactions()
                    ->latest('id')
                    ->limit(50)
                    ->get(['id', 'points', 'type', 'description', 'created_at'])
                    ->map(fn ($t) => [
                        'id' => $t->id,
                        'points' => (int) $t->points,
                        'type' => $t->type,
                        'description' => $t->description,
                        'created_at' => optional($t->created_at)?->toIso8601String(),
                    ])
                    ->values(),
            ],
        ]);
    }
}