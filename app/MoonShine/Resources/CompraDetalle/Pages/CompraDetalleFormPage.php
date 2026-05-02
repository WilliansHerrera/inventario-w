<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CompraDetalle\Pages;

use App\Models\Producto;
use App\MoonShine\Resources\CompraDetalle\CompraDetalleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Number;
use Throwable;

/**
 * @extends FormPage<CompraDetalleResource>
 */
class CompraDetalleFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        $currency = get_currency_symbol();

        return [
            Hidden::make('compra_id'),

            Box::make([
                Grid::make([
                    Column::make([
                        BelongsTo::make('Producto', 'producto', resource: ProductoResource::class)
                            ->nullable()
                            ->placeholder('Selecciona un producto...')
                            ->required()
                            ->searchable()
                            ->reactive(function (FieldsContract $fields, mixed $value) {
                                $id = $value instanceof Producto ? $value->id : $value;

                                if ($id) {
                                    $producto = Producto::find($id);

                                    if ($producto) {
                                        $fields->findByColumn('costo_unitario')?->setValue($producto->precio);
                                        $fields->findByColumn('costo_actual_referencia')?->setValue($producto->precio);
                                    }
                                }

                                return $fields;
                            }),
                    ])->columnSpan(12),

                    Column::make([
                        Number::make('Cantidad', 'cantidad')
                            ->required()
                            ->reactive()
                            ->min(1)
                            ->step(1)
                            ->default(1)
                            ->customAttributes([
                                'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                            ]),
                    ])->columnSpan(6),

                    Column::make([
                        Number::make('Costo Unitario (Factura)', 'costo_unitario')
                            ->required()
                            ->reactive()
                            ->min(0)
                            ->step(0.01)
                            ->prefix($currency)
                            ->hint('Monto en factura.')
                            ->customAttributes([
                                'onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46',
                            ]),
                    ])->columnSpan(6),

                    Column::make([
                        Number::make('Costo en Catálogo', 'costo_actual_referencia')
                            ->canApply(fn () => false)
                            ->onBeforeApply(fn () => null)
                            ->reactive()
                            ->readonly()
                            ->prefix($currency)
                            ->step(0.01)
                            ->hint('Precio actual en catálogo.')
                            ->customAttributes([
                                'style' => 'background-color: rgba(var(--primary), 0.05); border-style: dashed;',
                                'tabindex' => '-1',
                            ]),
                    ])->columnSpan(12),
                ]),

                Divider::make(),

                Number::make('Subtotal', 'subtotal')
                    ->readonly()
                    ->step(0.01)
                    ->prefix($currency)
                    ->changePreview(fn ($value) => format_currency((float) $value))
                    ->customAttributes([
                        'x-bind:value' => '(reactive.cantidad * reactive.costo_unitario).toFixed(2)',
                        'onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46',
                        'style' => 'font-weight: bold; font-size: 1.1em; color: rgba(var(--primary));',
                    ]),
            ]),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }

    /**
     * @param  FormBuilder  $component
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
    {
        return $component;
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
