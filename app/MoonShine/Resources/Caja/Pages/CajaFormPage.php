<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Caja\CajaResource;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;

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
                ->hint(get_currency_symbol() . ' ' . get_global_setting('currency_code')),
            Switcher::make('Abierta', 'abierta')->default(false),
            Text::make('Token de Sincronización', 'sync_token')
                ->readonly()
                ->hint('Usa este token para configurar la terminal POS de Windows.'),
        ];
    }
}
