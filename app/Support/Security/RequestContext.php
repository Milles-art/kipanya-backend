<?php

namespace App\Support\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class RequestContext
{
    public static function id(Request $request): string
    {
        return (string) ($request->attributes->get('request_id') ?? Str::uuid());
    }
}
