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
        'margen_defecto',
        'win_kiosk_mode',
        'win_debug_mode',
        'win_sync_interval',
        'win_auto_actualizar',
        'win_min_version',
        'win_auto_inicio',
        'win_default_ruta_datos',
        'receipt_header',
        'receipt_footer',
        'pos_block_without_shift',
        'default_opening_amount',
        'auto_open_shifts',
        'prices_include_tax',
    ];

    protected static function booted()
    {
        static::saved(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget('global_settings_v2');
        });

        static::deleted(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget('global_settings_v2');
        });
    }
}
