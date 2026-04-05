<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\GlobalSetting\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\GlobalSetting\GlobalSettingResource;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\ID;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\ColorManager\Palettes\CyanPalette;
use MoonShine\ColorManager\Palettes\GreenPalette;
use MoonShine\ColorManager\Palettes\YellowPalette;
use MoonShine\ColorManager\Palettes\OrangePalette;
use MoonShine\ColorManager\Palettes\PinkPalette;
use MoonShine\ColorManager\Palettes\RosePalette;
use MoonShine\ColorManager\Palettes\SkyPalette;
use MoonShine\ColorManager\Palettes\TealPalette;
use MoonShine\ColorManager\Palettes\GrayPalette;
use MoonShine\ColorManager\Palettes\NeutralPalette;
use MoonShine\ColorManager\Palettes\LimePalette;
use MoonShine\ColorManager\Palettes\HalloweenPalette;
use MoonShine\ColorManager\Palettes\RetroPalette;
use MoonShine\ColorManager\Palettes\SpringPalette;
use MoonShine\ColorManager\Palettes\ValentinePalette;
use MoonShine\ColorManager\Palettes\WinterPalette;

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
            'México' => ['locale' => 'es', 'currency_code' => 'MXN', 'currency_symbol' => '$'],
            'El Salvador' => ['locale' => 'es', 'currency_code' => 'USD', 'currency_symbol' => '$'],
            'España' => ['locale' => 'es', 'currency_code' => 'EUR', 'currency_symbol' => '€'],
            'USA' => ['locale' => 'en', 'currency_code' => 'USD', 'currency_symbol' => '$'],
            'Colombia' => ['locale' => 'es', 'currency_code' => 'COP', 'currency_symbol' => '$'],
            'Argentina' => ['locale' => 'es', 'currency_code' => 'ARS', 'currency_symbol' => '$'],
            'Chile' => ['locale' => 'es', 'currency_code' => 'CLP', 'currency_symbol' => '$'],
            'Perú' => ['locale' => 'es', 'currency_code' => 'PEN', 'currency_symbol' => 'S/'],
            'Ecuador' => ['locale' => 'es', 'currency_code' => 'USD', 'currency_symbol' => '$'],
            'Panamá' => ['locale' => 'es', 'currency_code' => 'USD', 'currency_symbol' => '$'],
            'Uruguay' => ['locale' => 'es', 'currency_code' => 'UYU', 'currency_symbol' => '$'],
            'Paraguay' => ['locale' => 'es', 'currency_code' => 'PYG', 'currency_symbol' => '₲'],
            'Bolivia' => ['locale' => 'es', 'currency_code' => 'BOB', 'currency_symbol' => 'Bs.'],
            'Costa Rica' => ['locale' => 'es', 'currency_code' => 'CRC', 'currency_symbol' => '₡'],
            'Guatemala' => ['locale' => 'es', 'currency_code' => 'GTQ', 'currency_symbol' => 'Q'],
            'Honduras' => ['locale' => 'es', 'currency_code' => 'HNL', 'currency_symbol' => 'L'],
            'Nicaragua' => ['locale' => 'es', 'currency_code' => 'NIO', 'currency_symbol' => 'C$'],
            'Dominicana' => ['locale' => 'es', 'currency_code' => 'DOP', 'currency_symbol' => 'RD$'],
            'Venezuela' => ['locale' => 'es', 'currency_code' => 'VES', 'currency_symbol' => 'Bs.'],
            'Brasil' => ['locale' => 'es', 'currency_code' => 'BRL', 'currency_symbol' => 'R$'],
            'Francia' => ['locale' => 'en', 'currency_code' => 'EUR', 'currency_symbol' => '€'],
            'Alemania' => ['locale' => 'en', 'currency_code' => 'EUR', 'currency_symbol' => '€'],
            'Canadá' => ['locale' => 'en', 'currency_code' => 'CAD', 'currency_symbol' => '$'],
            'Reino Unido' => ['locale' => 'en', 'currency_code' => 'GBP', 'currency_symbol' => '£'],
        ];

        return [
            Select::make('País', 'country_name')
                ->options(array_combine(array_keys($countryData), array_keys($countryData)))
                ->customAttributes([
                    '@change' => "
                        const data = " . json_encode($countryData) . ";
                        const val = \$event.target.value;
                        if (val && data[val]) {
                            const info = data[val];
                            if (typeof reactive !== 'undefined') {
                                reactive.locale = info.locale;
                                reactive.currency_code = info.currency_code;
                                reactive.currency_symbol = info.currency_symbol;
                            }
                        }
                    "
                ])
                ->reactive(function (\MoonShine\Contracts\Core\DependencyInjection\FieldsContract $fields, ?string $value) use ($countryData) {
                    if ($value && isset($countryData[$value])) {
                        $data = $countryData[$value];
                        
                        $fields->findByColumn('locale')?->setValue($data['locale']);
                        $fields->findByColumn('currency_code')?->setValue($data['currency_code']);
                        $fields->findByColumn('currency_symbol')?->setValue($data['currency_symbol']);
                    }

                    return $fields;
                })
                ->required(),

            Select::make('Idioma', 'locale')
                ->options([
                    'es' => 'Español',
                    'en' => 'English',
                ])
                ->required(),

            Select::make('Color de Interfaz', 'theme_palette')
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

            Text::make('Símbolo de Moneda', 'currency_symbol')
                ->default('$')
                ->required(),

            Text::make('Código de Moneda', 'currency_code')
                ->default('MXN')
                ->required(),

            \MoonShine\UI\Fields\Number::make('Margen de Ganancia Por Defecto (%)', 'margen_defecto')
                ->default(25)
                ->required(),

            \MoonShine\UI\Fields\Textarea::make('Cabecera del Ticket', 'receipt_header')
                ->hint('Texto superior del ticket (Ej: Nombre Tienda, Dirección, RFC)'),

            \MoonShine\UI\Fields\Textarea::make('Pie de Página', 'receipt_footer')
                ->default('¡Gracias por su compra!')
                ->hint('Texto al final del ticket'),
        ];
    }
}
