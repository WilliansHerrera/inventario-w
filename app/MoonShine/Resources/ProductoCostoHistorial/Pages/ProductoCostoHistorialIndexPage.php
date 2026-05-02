<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ProductoCostoHistorial\Pages;

use App\MoonShine\Resources\Compra\CompraResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\ProductoCostoHistorial\ProductoCostoHistorialResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use Throwable;

/**
 * @extends IndexPage<ProductoCostoHistorialResource>
 */
class ProductoCostoHistorialIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable()->columnSelection(false),
            Date::make('Fecha', 'created_at')
                ->format('d/m/Y H:i')
                ->sortable(),
            BelongsTo::make('Producto', 'producto', resource: ProductoResource::class)->columnSelection(false),
            Number::make('Costo Anterior', 'costo_anterior')
                ->changePreview(fn ($value) => format_currency((float) $value)),
            Number::make('Costo Nuevo', 'costo_nuevo')
                ->badge('green')
                ->changePreview(fn ($value) => format_currency((float) $value)),
            BelongsTo::make('Fuente (Compra)', 'compra', resource: CompraResource::class),
            BelongsTo::make('Usuario', 'user', resource: MoonShineUserResource::class),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component->columnSelection();
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
