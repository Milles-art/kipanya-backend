<?php

namespace App\Models\Administration;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'action', 'auditable_type', 'auditable_id', 'metadata',
        'ip_address', 'user_agent', 'request_id', 'created_at',
    ];

    /**
     * The audit trail is append-only. Refusing updates and deletes at the model layer
     * stops accidental or malicious edits made through the application. For real
     * tamper-resistance also restrict the database user to INSERT/SELECT on this table.
     */
    protected static function booted(): void
    {
        static::updating(static fn (): bool => false);
        static::deleting(static fn (): bool => false);
    }

    protected function casts(): array
    {
        return ['metadata' => 'array', 'created_at' => 'datetime'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
