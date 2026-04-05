<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Caja\CajaResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;

/**
 * @extends IndexPage<CajaResource>
 */
class CajaIndexPage extends IndexPage
{
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Nombre', 'nombre')->sortable()->required(),
            BelongsTo::make('Sucursal', 'sucursal', resource: LocaleResource::class)->sortable(),
            Number::make('Saldo', 'saldo')
                ->sortable()
                ->changePreview(fn($value) => format_currency((float) $value)),
            Switcher::make('Abierta', 'abierta'),
            Text::make('Token de Sincronización', 'sync_token')->readonly(),
        ];
    }
}
