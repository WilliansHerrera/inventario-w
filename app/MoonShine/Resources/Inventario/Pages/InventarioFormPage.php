<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Inventario\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Inventario\InventarioResource;
use MoonShine\UI\Fields\Number;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;

/**
 * @extends FormPage<InventarioResource>
 */
class InventarioFormPage extends FormPage
{
    protected function fields(): iterable
    {
        return [
            BelongsTo::make('Producto', 'producto', resource: \App\MoonShine\Resources\Producto\ProductoResource::class)->required(),
            BelongsTo::make('Local / Sucursal', 'locale', resource: \App\MoonShine\Resources\Locale\LocaleResource::class)->required(),
            Number::make('Stock', 'stock')->required()->default(0),
        ];
    }
}
