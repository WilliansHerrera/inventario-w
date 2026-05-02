<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto\Pages;

use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use App\Models\Producto;

use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends IndexPage<ProductoResource>
 */
class ProductoIndexPage extends IndexPage
{
    protected function topLayer(): array
    {
        return [
            ValueMetric::make('Total Productos en Catálogo')
                ->value(Producto::count())
                ->icon('shopping-bag')
        ];
    }

    protected function fields(): iterable
    {
        return [
            ID::make()->sortable()->columnSelection(false),
            Image::make('Foto', 'imagen')->disk('public'),
            Text::make('Nombre', 'nombre')->sortable()->required()->columnSelection(false),
            Text::make('Código de Producto', 'sku')->sortable()->required(),
            Text::make('Código de Barras', 'codigo_barra')->sortable(),
            Number::make('Costo', 'precio')
                ->sortable()
                ->step(0.01)
                ->changePreview(fn ($value) => format_currency((float) $value)),
            Number::make('Precio Venta', 'precio_venta')
                ->sortable()
                ->step(0.01)
                ->changePreview(fn ($value) => format_currency((float) $value)),
        ];
    }

    protected function modifyListComponent(ComponentContract $component): TableBuilder
    {
        return $component->columnSelection();
    }
}
