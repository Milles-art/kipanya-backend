<?php

namespace App\Models\Wear;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WearProductVariant extends Model
{
    use HasFactory;
    protected $fillable = ['wear_product_id', 'size', 'color', 'stock', 'sku'];

    protected function casts(): array
    {
        return ['stock' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(WearProduct::class, 'wear_product_id');
    }
}
