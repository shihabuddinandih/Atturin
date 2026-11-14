<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Pengaturan global platform berbentuk key-value, diubah oleh superadmin.
 */
class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::rememberForever(self::cacheKey($key), function () use ($key) {
            // Simpan penanda agar nilai "tidak ada" juga ikut di-cache.
            $row = static::query()->where('key', $key)->first();

            return $row ? ['v' => $row->value] : ['v' => null, 'missing' => true];
        });

        return isset($value['missing']) ? $default : $value['v'];
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        Cache::forget(self::cacheKey($key));
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private static function cacheKey(string $key): string
    {
        return 'app_setting:' . $key;
    }
}
