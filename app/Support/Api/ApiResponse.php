<?php

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

final class ApiResponse
{
    public static function success(mixed $data, string $message = 'Success.', int $status = 200, array $meta = []): JsonResponse
    {
        $payload = ['data' => $data, 'message' => $message];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, mixed $data, string $message = 'Success.'): JsonResponse
    {
        return self::success($data, $message, 200, [
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
