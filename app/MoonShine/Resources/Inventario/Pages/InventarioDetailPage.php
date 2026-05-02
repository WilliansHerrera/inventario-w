<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Inventario\Pages;

use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\InventarioMovimientoResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;

/**
 * @extends DetailPage<InventarioResource>
 */
class InventarioDetailPage extends DetailPage
{
    public function topLayer(): array
    {
        return [
            ActionButton::make(
                'Imprimir Código de Barras',
                fn () => route('admin.products.barcode', ['producto' => $this->getResource()->getItem()?->producto_id])
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
            BelongsTo::make('Producto', 'producto', resource: ProductoResource::class),
            BelongsTo::make('Local', 'locale', resource: LocaleResource::class),
            Number::make('Stock')
                ->badge(fn ($v) => $v <= 5 ? 'danger' : ($v <= 15 ? 'warning' : 'success')),
            HasMany::make('Movimientos', 'movimientos', resource: InventarioMovimientoResource::class),
        ];
    }
}
