<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Model;

final class StoreSetting extends Model
{
    protected $table = 'store_settings';

    protected $fillable = ['key', 'group', 'value'];
}
