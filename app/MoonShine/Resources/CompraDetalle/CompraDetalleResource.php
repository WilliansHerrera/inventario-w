<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CompraDetalle;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompraDetalle;
use App\MoonShine\Resources\CompraDetalle\Pages\CompraDetalleIndexPage;
use App\MoonShine\Resources\CompraDetalle\Pages\CompraDetalleFormPage;
use App\MoonShine\Resources\CompraDetalle\Pages\CompraDetalleDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<CompraDetalle, CompraDetalleIndexPage, CompraDetalleFormPage, CompraDetalleDetailPage>
 */
class CompraDetalleResource extends ModelResource
{
    protected string $model = CompraDetalle::class;

    protected string $title = 'CompraDetalles';
    
    protected function fields(): iterable
    {
        $currency = get_currency_symbol();

        return [
            \MoonShine\UI\Fields\ID::make()->sortable(),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Producto', 'producto', resource: \App\MoonShine\Resources\Producto\ProductoResource::class)
                ->required()
                ->searchable(),

            \MoonShine\UI\Fields\Number::make('Cantidad', 'cantidad')
                ->required(),

            \MoonShine\UI\Fields\Number::make('Costo en Catálogo', 'costo_actual_referencia')
                ->canApply(fn() => false)
                ->onBeforeApply(fn() => null)
                ->readonly()
                ->prefix($currency)
                ->step(0.01),

            \MoonShine\UI\Fields\Number::make('Costo Unitario (Factura)', 'costo_unitario')
                ->required()
                ->prefix($currency)
                ->step(0.01),

            \MoonShine\UI\Fields\Number::make('Subtotal', 'subtotal')
                ->readonly()
                ->prefix($currency)
                ->changePreview(fn($value) => format_currency((float) $value)),
        ];
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CompraDetalleIndexPage::class,
            CompraDetalleFormPage::class,
            CompraDetalleDetailPage::class,
        ];
    }
}
