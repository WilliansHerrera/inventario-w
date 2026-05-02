<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja\Pages;

use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends FormPage<CajaResource>
 */
class CajaFormPage extends FormPage
{
    protected function fields(): iterable
    {
        return [
            Text::make('Nombre', 'nombre')->required(),
            BelongsTo::make('Sucursal', 'sucursal', resource: LocaleResource::class)
                ->searchable()
                ->required(),
            Number::make('Saldo Initial', 'saldo')
                ->required()
                ->default(0)
                ->hint(get_currency_symbol().' '.get_global_setting('currency_code')),
            Switcher::make('Abierta', 'abierta')->default(false),
            Switcher::make('Inc. Apertura Global', 'incluir_en_apertura_global')->default(true)
                ->hint('Incluir en la apertura masiva de inicio de jornada.'),
            Switcher::make('Apertura Automática POS', 'apertura_automatica_pos')->default(false)
                ->hint('Si está activo, el POS abrirá la caja automáticamente sin pedir arqueo manual.'),
        ];
    }
}
