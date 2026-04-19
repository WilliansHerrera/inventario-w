<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ProductoCostoHistorial\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use App\MoonShine\Resources\ProductoCostoHistorial\ProductoCostoHistorialResource;
use MoonShine\Support\ListOf;
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
            \MoonShine\UI\Fields\ID::make()->sortable(),
            \MoonShine\UI\Fields\Date::make('Fecha', 'created_at')
                ->format('d/m/Y H:i')
                ->sortable(),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Producto', 'producto', resource: \App\MoonShine\Resources\Producto\ProductoResource::class),
            \MoonShine\UI\Fields\Number::make('Costo Anterior', 'costo_anterior')
                ->changePreview(fn($value) => format_currency((float) $value)),
            \MoonShine\UI\Fields\Number::make('Costo Nuevo', 'costo_nuevo')
                ->badge('green')
                ->changePreview(fn($value) => format_currency((float) $value)),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Fuente (Compra)', 'compra', resource: \App\MoonShine\Resources\Compra\CompraResource::class),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Usuario', 'user', resource: \App\MoonShine\Resources\MoonShineUser\MoonShineUserResource::class),
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
     *
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
}
