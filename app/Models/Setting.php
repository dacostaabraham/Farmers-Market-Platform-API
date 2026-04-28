<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /** Get a setting value by key, with optional default */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /** Set a setting value and clear cache */
    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting_{$key}");
    }

    // ── Convenience shortcuts ───────────────────────────────────────
    public static function creditInterestRate(): float
    {
        return (float) self::get('credit_interest_rate', 30); // default 30%
    }

    public static function commodityRateFcfa(): int
    {
        return (int) self::get('commodity_rate_fcfa', 1000); // default 1000 FCFA/kg
    }
}
