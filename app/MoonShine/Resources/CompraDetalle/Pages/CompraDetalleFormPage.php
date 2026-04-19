<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CompraDetalle\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use App\MoonShine\Resources\CompraDetalle\CompraDetalleResource;
use MoonShine\Support\ListOf;
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
            \MoonShine\UI\Fields\Hidden::make('compra_id'),

            \MoonShine\UI\Components\Layout\Box::make([
                \MoonShine\UI\Components\Layout\Grid::make([
                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Producto', 'producto', resource: \App\MoonShine\Resources\Producto\ProductoResource::class)
                            ->nullable()
                            ->placeholder('Selecciona un producto...')
                            ->required()
                            ->searchable()
                            ->reactive(function (\MoonShine\Contracts\Core\DependencyInjection\FieldsContract $fields, mixed $value) {
                                $id = $value instanceof \App\Models\Producto ? $value->id : $value;
                                
                                if ($id) {
                                    $producto = \App\Models\Producto::find($id);
                                    
                                    if ($producto) {
                                        $fields->findByColumn('costo_unitario')?->setValue($producto->precio);
                                        $fields->findByColumn('costo_actual_referencia')?->setValue($producto->precio);
                                    }
                                }

                                return $fields;
                            }),
                    ])->columnSpan(12),

                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\UI\Fields\Number::make('Cantidad', 'cantidad')
                            ->required()
                            ->reactive()
                            ->min(1)
                            ->step(1)
                            ->default(1)
                            ->customAttributes([
                                'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57'
                            ]),
                    ])->columnSpan(6),

                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\UI\Fields\Number::make('Costo Unitario (Factura)', 'costo_unitario')
                            ->required()
                            ->reactive()
                            ->min(0)
                            ->step(0.01)
                            ->prefix($currency)
                            ->hint('Monto en factura.')
                            ->customAttributes([
                                'onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46'
                            ]),
                    ])->columnSpan(6),

                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\UI\Fields\Number::make('Costo en Catálogo', 'costo_actual_referencia')
                            ->canApply(fn() => false)
                            ->onBeforeApply(fn() => null)
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

                \MoonShine\UI\Components\Layout\Divider::make(),

                \MoonShine\UI\Fields\Number::make('Subtotal', 'subtotal')
                    ->readonly()
                    ->step(0.01)
                    ->prefix($currency)
                    ->changePreview(fn($value) => format_currency((float) $value))
                    ->customAttributes([
                        'x-bind:value' => '(reactive.cantidad * reactive.costo_unitario).toFixed(2)',
                        'onkeypress' => 'return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46',
                        'style' => 'font-weight: bold; font-size: 1.1em; color: rgba(var(--primary));'
                    ]),
            ])
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
     *
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
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
