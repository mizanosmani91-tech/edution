<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * PlatformSetting
 *
 * সাধারণ key/value সেটিংস (boolean টগল মূলত) — সুপার এডমিন সেটিংস পেজ
 * থেকে নিয়ন্ত্রিত। Cache করা হয় যাতে বারবার query না লাগে।
 */
class PlatformSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = Cache::remember("platform_setting:{$key}", 300, function () use ($key) {
            return static::query()->find($key)?->value;
        });

        if ($value === null) {
            return $default;
        }

        return $value === '1';
    }

    public static function setBool(string $key, bool $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value ? '1' : '0']);
        Cache::forget("platform_setting:{$key}");
    }
}
