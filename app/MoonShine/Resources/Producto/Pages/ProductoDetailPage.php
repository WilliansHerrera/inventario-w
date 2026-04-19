<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto\Pages;

use MoonShine\Laravel\Pages\Crud\DetailPage;
use App\MoonShine\Resources\Producto\ProductoResource;

/**
 * @extends DetailPage<ProductoResource>
 */
class ProductoDetailPage extends DetailPage
{
    public function topLayer(): array
    {
        return [
            \MoonShine\UI\Components\ActionButton::make(
                'Imprimir Código de Barras',
                fn() => route('admin.products.barcode', ['producto' => $this->getResource()->getItem()?->getKey()])
            )
            ->icon('qr-code')
            ->blank()
            ->primary()
            ->customAttributes(['class' => 'mb-4'])
        ];
    }

    protected function fields(): iterable
    {
        return [
            \MoonShine\UI\Fields\ID::make(),
            \MoonShine\UI\Fields\Image::make('Imagen', 'imagen')->disk('public'),
            \MoonShine\UI\Fields\Text::make('Nombre', 'nombre'),
            \MoonShine\UI\Fields\Text::make('SKU / Código Interno', 'sku'),
            \MoonShine\UI\Fields\Text::make('Código de Barras', 'codigo_barra')
                ->badge('primary'),
            \MoonShine\UI\Fields\Number::make('Precio de Venta', 'precio_venta')
                ->changePreview(fn($v) => format_currency($v)),
        ];
    }
}
