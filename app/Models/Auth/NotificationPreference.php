<?php

namespace App\Models\Auth;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'push_enabled', 'email_enabled', 'sms_enabled', 'marketing_enabled', 'restock_enabled', 'price_drop_enabled'];

    protected function casts(): array
    {
        return [
            'push_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'sms_enabled' => 'boolean',
            'marketing_enabled' => 'boolean',
            'restock_enabled' => 'boolean',
            'price_drop_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
