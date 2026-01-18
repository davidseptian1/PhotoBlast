<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getString(string $key, ?string $default = null): ?string
    {
        $row = self::query()->where('key', $key)->first();
        if (!$row) return $default;
        return $row->value ?? $default;
    }

    public static function getFloat(string $key, float $default): float
    {
        $value = self::getString($key, null);
        if ($value === null || $value === '') return $default;
        if (!is_numeric($value)) return $default;
        return (float) $value;
    }

    public static function setString(string $key, ?string $value): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
