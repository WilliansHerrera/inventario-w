<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Image;

/**
 * @extends IndexPage<ProductoResource>
 */
class ProductoIndexPage extends IndexPage
{
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Image::make('Foto', 'imagen')->disk('public'),
            Text::make('Nombre', 'nombre')->sortable()->required(),
            Text::make('Código de Producto', 'sku')->sortable()->required(),
            Text::make('Código de Barras', 'codigo_barra')->sortable(),
            Number::make('Costo', 'precio')
                ->sortable()
                ->step(0.01)
                ->changePreview(fn($value) => format_currency((float) $value)),
            Number::make('Precio Venta', 'precio_venta')
                ->sortable()
                ->step(0.01)
                ->changePreview(fn($value) => format_currency((float) $value)),
        ];
    }
}
