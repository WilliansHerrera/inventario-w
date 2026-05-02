<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Inventario\Pages;

use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends IndexPage<InventarioResource>
 */
class InventarioIndexPage extends IndexPage
{
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable()->columnSelection(false),
            BelongsTo::make(__('Producto'), 'producto', resource: ProductoResource::class)
                ->sortable()
                ->columnSelection(false),
            BelongsTo::make(__('Sucursal'), 'locale', resource: LocaleResource::class)->sortable(),
            Text::make(__('Precio Venta'), 'producto.precio_venta')
                ->changePreview(fn ($v) => format_currency($v)),
            Number::make(__('Stock'), 'stock')
                ->sortable()
                ->badge(fn ($v) => $v <= 5 ? 'danger' : ($v <= 15 ? 'warning' : 'success')),
        ];
    }

    protected function modifyListComponent(ComponentContract $component): TableBuilder
    {
        return $component->columnSelection();
    }
}
