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
        'iva_porcentaje',
        'cash_management_mode',
    ];

    protected static function booted()
    {
        static::saved(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget('global_settings_v2');

            if ($setting->cash_management_mode === 'express') {
                $locales = \App\Models\Locale::all();
                foreach ($locales as $locale) {
                    if ($locale->cajas()->count() === 0) {
                        $locale->cajas()->create([
                            'nombre' => 'Caja Única',
                            'saldo' => 0,
                            'abierta' => true, // En Express, dejamos la caja operativa por defecto
                        ]);
                    }
                }
            }
        });

        static::deleted(function ($setting) {
            \Illuminate\Support\Facades\Cache::forget('global_settings_v2');
        });
    }
}
