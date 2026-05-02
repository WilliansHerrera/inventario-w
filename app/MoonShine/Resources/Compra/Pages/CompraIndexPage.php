<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Compra\Pages;

use App\MoonShine\Resources\Compra\CompraResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Proveedor\ProveedorResource;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
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
            ID::make()->sortable()->columnSelection(false),
            Date::make(__('Fecha'), 'created_at')
                ->format('d/m/Y H:i')
                ->sortable(),
            Text::make(__('N° Documento'), 'nro_documento')->sortable()->columnSelection(false),
            BelongsTo::make(__('Proveedor'), 'proveedor', resource: ProveedorResource::class),
            BelongsTo::make(__('Local'), 'locale', resource: LocaleResource::class),
            Number::make(__('Total'), 'total')
                ->changePreview(fn ($value) => format_currency((float) $value))
                ->sortable(),
            Text::make(__('Estado'), 'estado')
                ->badge(fn ($val) => $val === 'completada' ? 'green' : 'gray'),
        ];
    }

    protected function buttons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, [
            ...parent::buttons()->toArray(),
            ActionButton::make(
                __('Procesar'),
            )
                ->method('completarCompra')
                ->primary()
                ->icon('check-circle')
                ->canSee(fn (mixed $item) => $item->estado === 'borrador')
                ->withConfirm(
                    __('¿Confirmar Recepción?'),
                    __('Esto incrementará el stock de los productos y actualizará los costos del sistema de forma permanente.'),
                    __('Proceder')
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
