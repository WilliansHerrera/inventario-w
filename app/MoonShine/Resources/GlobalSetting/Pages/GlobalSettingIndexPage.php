<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\GlobalSetting\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
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
 * @extends IndexPage<GlobalSettingResource>
 */
class GlobalSettingIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Select::make('País', 'country_name')
                ->options([
                    'México' => 'México',
                    'El Salvador' => 'El Salvador',
                    'España' => 'España',
                    'USA' => 'USA',
                    'Colombia' => 'Colombia',
                    'Argentina' => 'Argentina',
                    'Chile' => 'Chile',
                    'Perú' => 'Perú',
                    'Ecuador' => 'Ecuador',
                    'Panamá' => 'Panamá',
                    'Uruguay' => 'Uruguay',
                    'Paraguay' => 'Paraguay',
                    'Bolivia' => 'Bolivia',
                    'Costa Rica' => 'Costa Rica',
                    'Guatemala' => 'Guatemala',
                    'Honduras' => 'Honduras',
                    'Nicaragua' => 'Nicaragua',
                    'Dominicana' => 'Dominicana',
                    'Venezuela' => 'Venezuela',
                    'Brasil' => 'Brasil',
                    'Francia' => 'Francia',
                    'Alemania' => 'Alemania',
                    'Canadá' => 'Canadá',
                    'Reino Unido' => 'Reino Unido',
                ]),

            Select::make('Idioma', 'locale')
                ->options([
                    'es' => 'Español',
                    'en' => 'English',
                ]),

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
                ]),

            Text::make('Símbolo', 'currency_symbol'),
            Text::make('Código', 'currency_code'),
        ];
    }
}
