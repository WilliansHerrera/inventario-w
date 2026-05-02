<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto\Pages;

use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\ProductoCostoHistorial\ProductoCostoHistorialResource;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<ProductoResource>
 */
class ProductoDetailPage extends DetailPage
{
    public function topLayer(): array
    {
        return [
            ActionButton::make(
                'Imprimir Código de Barras',
                fn () => route('admin.products.barcode', ['producto' => $this->getResource()->getItem()?->getKey()])
            )
                ->icon('qr-code')
                ->blank()
                ->primary()
                ->customAttributes(['class' => 'mb-4']),
        ];
    }

    protected function fields(): iterable
    {
        return [
            ID::make(),
            Image::make('Imagen Principal', 'imagen')->disk('public'),
            Image::make('Galería', 'galeria')->disk('public')->multiple(),
            Text::make('Nombre', 'nombre'),
            Text::make('SKU / Código Interno', 'sku'),
            Text::make('Código de Barras', 'codigo_barra')
                ->badge('primary'),
            Number::make('Precio de Venta', 'precio_venta')
                ->changePreview(fn ($v) => format_currency($v)),

            HasMany::make('Historial de Costos', 'costoHistorials', resource: ProductoCostoHistorialResource::class)
                ->creatable(false)
                ->onlyLink(),
        ];
    }
}
