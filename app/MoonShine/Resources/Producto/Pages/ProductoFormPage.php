<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Image;

/**
 * @extends FormPage<ProductoResource>
 */
class ProductoFormPage extends FormPage
{
    protected function fields(): iterable
    {
        return [
            Image::make('Foto', 'imagen')->dir('productos'),
            Text::make('Nombre', 'nombre')->required(),
            Text::make('Código de Producto', 'sku')
                ->default(fn() => 'INV-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)))
                ->hint('Auto-generado: INV-AAMMDD-XXXX'),
            Textarea::make('Descripción', 'descripcion'),
            Number::make('Costo', 'precio')
                ->required()
                ->hint(get_currency_symbol() . ' ' . get_global_setting('currency_code'))
                ->reactive(function (\MoonShine\Contracts\Core\DependencyInjection\FieldsContract $fields, ?string $value) {
                    $margin = (float) get_global_setting('margen_defecto', 25);
                    $cost = (float) $value;
                    $sellingPrice = $cost * (1 + $margin / 100);
                    
                    $fields->findByColumn('precio_venta')?->setValue(number_format($sellingPrice, 2, '.', ''));

                    return $fields;
                }),

            Number::make('Precio Venta', 'precio_venta')
                ->required()
                ->hint(get_currency_symbol() . ' ' . get_global_setting('currency_code')),
        ];
    }
}
