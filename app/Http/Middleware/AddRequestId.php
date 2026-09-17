<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AddRequestId
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->header('X-Request-ID');
        $requestId = is_string($provided) && Str::isUuid($provided) ? $provided : (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
