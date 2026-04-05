<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Inventario\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Inventario\InventarioResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;

use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<InventarioResource>
 */
class InventarioIndexPage extends IndexPage
{
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Producto', 'producto', resource: \App\MoonShine\Resources\Producto\ProductoResource::class)->sortable(),
            BelongsTo::make('Sucursal', 'locale', resource: \App\MoonShine\Resources\Locale\LocaleResource::class)->sortable(),
            Text::make('Precio Venta', 'producto.precio_venta')
                ->changePreview(fn($v) => format_currency($v)),
            Number::make('Stock', 'stock')
                ->sortable()
                ->badge(fn($v) => $v <= 5 ? 'danger' : ($v <= 15 ? 'warning' : 'success')),
        ];
    }
}
