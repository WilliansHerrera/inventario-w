<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\GlobalSetting\Pages;

use App\MoonShine\Resources\GlobalSetting\GlobalSettingResource;
use MoonShine\ColorManager\Palettes\CyanPalette;
use MoonShine\ColorManager\Palettes\GrayPalette;
use MoonShine\ColorManager\Palettes\GreenPalette;
use MoonShine\ColorManager\Palettes\HalloweenPalette;
use MoonShine\ColorManager\Palettes\LimePalette;
use MoonShine\ColorManager\Palettes\NeutralPalette;
use MoonShine\ColorManager\Palettes\OrangePalette;
use MoonShine\ColorManager\Palettes\PinkPalette;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\ColorManager\Palettes\RetroPalette;
use MoonShine\ColorManager\Palettes\RosePalette;
use MoonShine\ColorManager\Palettes\SkyPalette;
use MoonShine\ColorManager\Palettes\SpringPalette;
use MoonShine\ColorManager\Palettes\TealPalette;
use MoonShine\ColorManager\Palettes\ValentinePalette;
use MoonShine\ColorManager\Palettes\WinterPalette;
use MoonShine\ColorManager\Palettes\YellowPalette;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<GlobalSettingResource>
 */
class GlobalSettingFormPage extends FormPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        $countryData = [
            'México' => ['locale' => 'es', 'currency_code' => 'MXN', 'currency_symbol' => '$', 'iva' => 16.00],
            'El Salvador' => ['locale' => 'es', 'currency_code' => 'USD', 'currency_symbol' => '$', 'iva' => 13.00],
            'España' => ['locale' => 'es', 'currency_code' => 'EUR', 'currency_symbol' => '€', 'iva' => 21.00],
            'USA' => ['locale' => 'en', 'currency_code' => 'USD', 'currency_symbol' => '$', 'iva' => 0.00],
            'Colombia' => ['locale' => 'es', 'currency_code' => 'COP', 'currency_symbol' => '$', 'iva' => 19.00],
            'Argentina' => ['locale' => 'es', 'currency_code' => 'ARS', 'currency_symbol' => '$', 'iva' => 21.00],
            'Chile' => ['locale' => 'es', 'currency_code' => 'CLP', 'currency_symbol' => '$', 'iva' => 19.00],
            'Perú' => ['locale' => 'es', 'currency_code' => 'PEN', 'currency_symbol' => 'S/', 'iva' => 18.00],
            'Ecuador' => ['locale' => 'es', 'currency_code' => 'USD', 'currency_symbol' => '$', 'iva' => 15.00],
            'Panamá' => ['locale' => 'es', 'currency_code' => 'USD', 'currency_symbol' => '$', 'iva' => 7.00],
            'Uruguay' => ['locale' => 'es', 'currency_code' => 'UYU', 'currency_symbol' => '$', 'iva' => 22.00],
            'Paraguay' => ['locale' => 'es', 'currency_code' => 'PYG', 'currency_symbol' => '₲', 'iva' => 10.00],
            'Bolivia' => ['locale' => 'es', 'currency_code' => 'BOB', 'currency_symbol' => 'Bs.', 'iva' => 13.00],
            'Costa Rica' => ['locale' => 'es', 'currency_code' => 'CRC', 'currency_symbol' => '₡', 'iva' => 13.00],
            'Guatemala' => ['locale' => 'es', 'currency_code' => 'GTQ', 'currency_symbol' => 'Q', 'iva' => 12.00],
            'Honduras' => ['locale' => 'es', 'currency_code' => 'HNL', 'currency_symbol' => 'L', 'iva' => 15.00],
            'Nicaragua' => ['locale' => 'es', 'currency_code' => 'NIO', 'currency_symbol' => 'C$', 'iva' => 15.00],
            'Dominicana' => ['locale' => 'es', 'currency_code' => 'DOP', 'currency_symbol' => 'RD$', 'iva' => 18.00],
            'Venezuela' => ['locale' => 'es', 'currency_code' => 'VES', 'currency_symbol' => 'Bs.', 'iva' => 16.00],
            'Brasil' => ['locale' => 'es', 'currency_code' => 'BRL', 'currency_symbol' => 'R$', 'iva' => 17.00],
        ];

        return [
            Select::make(__('País'), 'country_name')
                ->options(array_combine(array_keys($countryData), array_keys($countryData)))
                ->reactive(function (FieldsContract $fields, ?string $value) use ($countryData) {
                    if ($value && isset($countryData[$value])) {
                        $data = $countryData[$value];

                        $fields->findByColumn('locale')?->setValue($data['locale']);
                        $fields->findByColumn('currency_code')?->setValue($data['currency_code']);
                        $fields->findByColumn('currency_symbol')?->setValue($data['currency_symbol']);
                        $fields->findByColumn('iva_porcentaje')?->setValue($data['iva']);
                    }

                    return $fields;
                })
                ->required(),

            Select::make(__('Idioma'), 'locale')
                ->options([
                    'es' => __('Español'),
                    'en' => __('English'),
                    'pt' => __('Português'),
                    'fr' => __('Français'),
                ])
                ->required(),

            Select::make(__('Color de Interfaz'), 'theme_palette')
                ->options([
                    PurplePalette::class => 'Purple',
                    CyanPalette::class => 'Cyan',
                    GreenPalette::class => 'Green',
                    YellowPalette::class => 'Yellow',
                    OrangePalette::class => 'Orange',
                    PinkPalette::class => 'Pink',
                    RosePalette::class => 'Rose',
                    SkyPalette::class => 'Sky',
                    TealPalette::class => 'Teal',
                    GrayPalette::class => 'Gray',
                    NeutralPalette::class => 'Neutral',
                    LimePalette::class => 'Lime',
                    HalloweenPalette::class => 'Halloween',
                    RetroPalette::class => 'Retro',
                    SpringPalette::class => 'Spring',
                    ValentinePalette::class => 'Valentine',
                    WinterPalette::class => 'Winter',
                ])
                ->required(),

            Text::make(__('Símbolo de Moneda'), 'currency_symbol')
                ->default('$')
                ->reactive()
                ->required(),

            Text::make(__('Código de Moneda'), 'currency_code')
                ->default('MXN')
                ->reactive()
                ->required(),

            Number::make(__('Margen de Ganancia Por Defecto (%)'), 'margen_defecto')
                ->default(25)
                ->required(),

            Number::make(__('Porcentaje de Impuesto (IVA %)'), 'iva_porcentaje')
                ->default(13)
                ->step(0.01)
                ->reactive()
                ->required()
                ->hint(__('Tasa de impuesto aplicada temporalmente a las compras.')),

            Switcher::make(__('¿Los costos ingresados YA incluyen IVA?'), 'prices_include_tax')
                ->default(false)
                ->hint(__('Si se activa, el sistema desglosará el IVA del precio ingresado. Si se desactiva, el sistema sumará el IVA al precio.')),

            Divider::make(__('Gestión de Cajas y POS')),

            Select::make(__('Modo de Gestión de Cajas'), 'cash_management_mode')
                ->options([
                    'express' => __('Modo Caja Única (Simple)'),
                    'industrial' => __('Modo Multicaja (Avanzado)'),
                ])
                ->default('express')
                ->required()
                ->hint(__('El modo Simple es para tiendas con un solo terminal. El modo Avanzado permite múltiples cajas, sucursales y auditorías.')),

            Switcher::make(__('Modo Kiosko por Defecto'), 'win_kiosk_mode')
                ->hint(__('Obligar a la App de Windows a iniciar en pantalla completa')),

            Switcher::make(__('Modo Desarrollo por Defecto'), 'win_debug_mode')
                ->hint(__('Permite ver herramientas de desarrollador en la App')),

            Number::make(__('Intervalo Sincronización (Segundos)'), 'win_sync_interval')
                ->default(60)
                ->required()
                ->hint(__('Tiempo entre actualizaciones de datos en modo offline')),

            Switcher::make(__('Auto-actualizar App'), 'win_auto_actualizar')
                ->default(true),

            Text::make(__('Versión Mínima Requerida'), 'win_min_version')
                ->default('1.0.0')
                ->hint(__('Bloquea el acceso a versiones anteriores a esta')),

            Switcher::make(__('Iniciar con Windows'), 'win_auto_inicio')
                ->default(true),

            Text::make(__('Ruta de Datos por Defecto'), 'win_default_ruta_datos')
                ->default('C:\POS\Data')
                ->hint(__('Donde la App de escritorio guardará la base de datos local')),

            Divider::make(__('Ajustes del Ticket (Recibo)')),

            Textarea::make(__('Cabecera del Ticket'), 'receipt_header')
                ->hint(__('Texto superior del ticket (Ej: Nombre Tienda, Dirección, RFC)')),

            Textarea::make(__('Pie de Página'), 'receipt_footer')
                ->default('¡Gracias por su compra!')
                ->hint(__('Texto al final del ticket')),

            Divider::make(__('Seguridad y Auditoría')),

            Switcher::make(__('Bloquear POS sin Turno Abierto'), 'pos_block_without_shift')
                ->default(false)
                ->hint(__('Si se activa, el terminal POS no permitirá realizar ventas ni búsquedas si no hay una jornada de caja iniciada.')),

            Number::make(__('Monto de Apertura Global ($)'), 'default_opening_amount')
                ->default(50)
                ->hint(__('Es el dinero que se asignará automáticamente a todas las cajas al usar el botón "Iniciar Jornada Única".')),

            Switcher::make(__('Apertura Automática de Caja'), 'auto_open_shifts')
                ->default(false)
                ->hint(__('Si se activa, el turno de caja se abrirá automáticamente al entrar al terminal POS usando el monto de apertura global.')),
        ];
    }
}
