<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Model;

final class StorefrontSetting extends Model
{
    protected $table = 'storefront_settings';

    protected $fillable = ['key', 'group', 'value'];

    public static function value(string $key, mixed $default = null): mixed
    {
        $value = static::query()->where('key', $key)->value('value');

        return $value === null ? $default : $value;
    }

    public static function json(string $key, array $default = []): array
    {
        $value = static::value($key);
        if (! is_string($value) || $value === '') {
            return $default;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_numeric')) : $default;
    }
}
