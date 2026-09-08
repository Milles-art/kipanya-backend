<?php

namespace App\Models\Administration;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'application', 'subject_type', 'subject_id', 'ip_address'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
