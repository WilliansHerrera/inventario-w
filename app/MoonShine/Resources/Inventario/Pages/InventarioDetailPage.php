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
    protected function fields(): iterable
    {
        return [
            \MoonShine\UI\Fields\ID::make(),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Producto', 'producto'),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Local', 'locale'),
            \MoonShine\UI\Fields\Number::make('Stock'),
            \MoonShine\Laravel\Fields\Relationships\HasMany::make('Historial de Stock', 'movimientos'),
        ];
    }
}
