<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CompraDetalle;

use App\Models\CompraDetalle;
use App\MoonShine\Resources\CompraDetalle\Pages\CompraDetalleDetailPage;
use App\MoonShine\Resources\CompraDetalle\Pages\CompraDetalleFormPage;
use App\MoonShine\Resources\CompraDetalle\Pages\CompraDetalleIndexPage;
use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;

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
            ID::make()->sortable(),
            BelongsTo::make('Producto', 'producto', resource: ProductoResource::class)
                ->required()
                ->searchable(),

            Number::make('Cantidad', 'cantidad')
                ->required(),

            Number::make('Costo en Catálogo', 'costo_actual_referencia')
                ->canApply(fn () => false)
                ->onBeforeApply(fn () => null)
                ->readonly()
                ->prefix($currency)
                ->step(0.01),

            Number::make('Costo Unitario (Factura)', 'costo_unitario')
                ->required()
                ->prefix($currency)
                ->step(0.01),

            Number::make('Subtotal', 'subtotal')
                ->readonly()
                ->prefix($currency)
                ->changePreview(fn ($value) => format_currency((float) $value)),
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

    public function getTitle(): string
    {
        return __($this->title);
    }
}
