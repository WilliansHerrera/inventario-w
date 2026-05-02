<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_block_without_shift',
        'default_opening_amount',
        'auto_open_shifts',
        'cash_management_mode',
        'country_name',
        'locale',
        'currency_code',
        'currency_symbol',
        'iva_porcentaje',
        'prices_include_tax',
        'theme_palette',
        'receipt_header',
        'receipt_footer',
        'margen_defecto',
        'win_kiosk_mode',
        'win_debug_mode',
        'win_sync_interval',
        'win_auto_actualizar',
        'win_min_version',
        'win_auto_inicio',
        'win_default_ruta_datos',
    ];
}
