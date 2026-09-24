<?php

namespace App\Http\Controllers\Api\V1\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

final class AccountSecurityController extends Controller
{
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $hasCurrent = $user->password !== null;

        $data = Validator::make($request->all(), [
            'current_password' => [$hasCurrent ? 'required' : 'nullable', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        if ($hasCurrent && ! Hash::check($data['current_password'] ?? '', $user->password)) {
            abort(422, 'The current password is incorrect.');
        }

        // The `password` attribute uses Laravel's `hashed` cast, which hashes on
        // assignment, so a plain-text value is stored (never double-hashed).
        // Assign directly + save (rather than `update()`) so the cast runs once.
        $user->password = $data['new_password'];
        $user->save();

        return response()->json(['message' => 'Password updated.']);
    }

    public function sessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentId = $user->currentAccessToken()?->id;

        $sessions = $user->tokens()
            ->orderByDesc('last_used_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'device' => $token->name,
                'is_current' => $currentId !== null && (int) $token->id === (int) $currentId,
                'ip_address' => null,
                'last_active' => $token->last_used_at ?? $token->created_at,
            ])
            ->values();

        return response()->json(['data' => $sessions]);
    }

    public function revokeSession(Request $request, int $session): JsonResponse
    {
        $user = $request->user();
        $token = $user->tokens()->findOrFail($session);

        if ($user->currentAccessToken() && $token->id === $user->currentAccessToken()->id) {
            abort(422, 'The current session cannot be revoked.');
        }

        $token->delete();

        return response()->json(['data' => ['revoked' => true]]);
    }
}