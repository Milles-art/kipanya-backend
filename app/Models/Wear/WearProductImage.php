<?php

namespace App\Models\Wear;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WearProductImage extends Model
{
    protected $fillable = [
        'wear_product_id',
        'path',
        'role',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(WearProduct::class, 'wear_product_id');
    }

    public function getUrlAttribute(): string
    {
        if (preg_match('/^https?:\/\//i', $this->path)) {
            return $this->path;
        }

        $relative = ltrim($this->path, '/');

        return file_exists(public_path($relative))
            ? asset($relative)
            : asset('assets/wear/catalog/placeholder.svg');
    }
}
