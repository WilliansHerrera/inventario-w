<?php

use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('get_global_setting')) {
    function get_global_setting(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('global_settings', function () {
            return GlobalSetting::first();
        });

        if (!$settings) {
            return $default;
        }

        return $settings->{$key} ?? $default;
    }
}

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol(): string
    {
        return get_global_setting('currency_symbol', '$');
    }
}

if (!function_exists('format_currency')) {
    function format_currency(mixed $amount): string
    {
        $symbol = get_currency_symbol();
        return $symbol . ' ' . number_format((float) $amount, 2);
    }
}
