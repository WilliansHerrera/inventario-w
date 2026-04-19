<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Compra\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use App\MoonShine\Resources\Compra\CompraResource;
use MoonShine\Support\ListOf;
use Throwable;


/**
 * @extends IndexPage<CompraResource>
 */
class CompraIndexPage extends IndexPage
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
            \MoonShine\UI\Fields\Text::make('N° Documento', 'nro_documento')->sortable(),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Proveedor', 'proveedor', resource: \App\MoonShine\Resources\Proveedor\ProveedorResource::class),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Local', 'locale', resource: \App\MoonShine\Resources\Locale\LocaleResource::class),
            \MoonShine\UI\Fields\Number::make('Total', 'total')
                ->changePreview(fn($value) => format_currency((float) $value))
                ->sortable(),
            \MoonShine\UI\Fields\Text::make('Estado', 'estado')
                ->badge(fn($val) => $val === 'completada' ? 'green' : 'gray'),
        ];
    }

    protected function buttons(): ListOf
    {
        return new ListOf(\MoonShine\Contracts\UI\ActionButtonContract::class, [
            ...parent::buttons()->toArray(),
            \MoonShine\UI\Components\ActionButton::make(
                'Procesar',
            )
            ->method('completarCompra')
            ->primary()
            ->icon('check-circle')
            ->canSee(fn (mixed $item) => $item->estado === 'borrador')
            ->withConfirm(
                '¿Confirmar Recepción?',
                'Esto incrementará el stock de los productos y actualizará los costos del sistema de forma permanente.',
                'Proceder'
            )
            ->showInLine(),
        ]);
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
