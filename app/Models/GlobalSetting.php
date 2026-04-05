<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    protected $fillable = [
        'country_name',
        'locale',
        'currency_code',
        'currency_symbol',
        'theme_palette',
        'receipt_header',
        'receipt_footer',
    ];

    protected static function booted()
    {
        static::saved(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget('global_settings');
        });

        static::deleted(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget('global_settings');
        });
    }
}
