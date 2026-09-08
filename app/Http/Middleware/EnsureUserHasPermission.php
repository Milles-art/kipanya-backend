<?php

namespace App\Http\Middleware;

use App\Services\Authorization\AuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasPermission
{
    public function __construct(
        private readonly AuthorizationService $authorization,
    ) {
    }

    public function handle(
        Request $request,
        Closure $next,
        string $permission,
    ): Response {
        $user = $request->user();

        if (! $user || ! $this->authorization->hasPermission($user, $permission)) {
            return response()->json([
                'message' => 'You are not authorized to perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
