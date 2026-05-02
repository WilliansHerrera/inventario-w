<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Inventario\Pages;

use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Fields\Number;

/**
 * @extends FormPage<InventarioResource>
 */
class InventarioFormPage extends FormPage
{
    protected function fields(): iterable
    {
        return [
            BelongsTo::make('Producto', 'producto', resource: ProductoResource::class)->required(),
            BelongsTo::make('Local / Sucursal', 'locale', resource: LocaleResource::class)->required(),
            Number::make('Stock', 'stock')->required()->default(0),
        ];
    }
}
