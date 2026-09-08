<?php

namespace App\Support;

use App\Models\Administration\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class AuditLogger
{
    public function log(?Request $request, string $action, ?Model $auditable = null, array $metadata = []): AuditLog
    {
        return AuditLog::create([
            'actor_id' => $request?->user()?->id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'request_id' => $request ? (string) ($request->attributes->get('request_id') ?? Str::uuid()) : null,
        ]);
    }
}
