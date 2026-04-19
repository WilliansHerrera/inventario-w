<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Inventario\Pages;

use MoonShine\Laravel\Pages\Crud\DetailPage;
use App\MoonShine\Resources\Inventario\InventarioResource;

/**
 * @extends DetailPage<InventarioResource>
 */
class InventarioDetailPage extends DetailPage
{
    public function topLayer(): array
    {
        return [
            \MoonShine\UI\Components\ActionButton::make(
                'Imprimir Código de Barras',
                fn() => route('admin.products.barcode', ['producto' => $this->getResource()->getItem()?->producto_id])
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
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Producto', 'producto', resource: \App\MoonShine\Resources\Producto\ProductoResource::class),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Local', 'locale', resource: \App\MoonShine\Resources\Locale\LocaleResource::class),
            \MoonShine\UI\Fields\Number::make('Stock')
                ->badge(fn($v) => $v <= 5 ? 'danger' : ($v <= 15 ? 'warning' : 'success')),
            \MoonShine\Laravel\Fields\Relationships\HasMany::make('Movimientos', 'movimientos', resource: \App\MoonShine\Resources\InventarioMovimientoResource::class),
        ];
    }
}
