<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Compra\Pages;

use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use App\MoonShine\Resources\Compra\CompraResource;
use MoonShine\Crud\Components\Fragment;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Support\ListOf;
use Throwable;

/**
 * @extends FormPage<CompraResource>
 */
class CompraFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        $item = $this->getResource()->getItem();
        $isCompleted = $item && $item->estado === 'completada';
        $iva = get_global_setting('iva_porcentaje', 13.00);
        $isNew = ! $this->getResource()->getItemID();

        return [
            \MoonShine\UI\Components\Layout\Box::make('Datos de la Compra', [
                \MoonShine\UI\Components\Layout\Grid::make([
                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Proveedor', 'proveedor', resource: \App\MoonShine\Resources\Proveedor\ProveedorResource::class)
                            ->required()
                            ->searchable()
                            ->disabled($isCompleted),
                    ])->columnSpan(6),

                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Local Destino', 'locale', resource: \App\MoonShine\Resources\Locale\LocaleResource::class)
                            ->required()
                            ->disabled($isCompleted),
                    ])->columnSpan(6),

                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\UI\Fields\Text::make('N° Documento (Factura/Nota)', 'nro_documento')
                            ->disabled($isCompleted),
                    ])->columnSpan(6),

                    \MoonShine\UI\Components\Layout\Column::make([
                        \MoonShine\UI\Fields\Text::make('Estado', 'estado')
                            ->badge(fn($val) => $val === 'completada' ? 'green' : 'gray')
                            ->fill('borrador')
                            ->disabled(),
                    ])->columnSpan(6),
                ])
            ]),

            \MoonShine\UI\Components\Layout\Divider::make('Detalle de Productos'),

            \MoonShine\UI\Components\Alert::make(
                'information-circle',
                'primary'
            )
            ->content('Primero guarda la información de la cabecera para habilitar el ingreso de productos.')
            ->canSee(fn() => $isNew),

            \MoonShine\Laravel\Fields\Relationships\HasMany::make('Productos', 'detalles', resource: \App\MoonShine\Resources\CompraDetalle\CompraDetalleResource::class)
                ->creatable(!$isCompleted)
                ->nullable()
                ->modifyTable(fn(TableBuilder $table) => $table->async(events: ['fragment_updated:resumen-compra'])),

            Fragment::make([
                \MoonShine\UI\Components\Layout\Box::make('Resumen Financiero', [
                    \MoonShine\UI\Components\Layout\Grid::make([
                        \MoonShine\UI\Components\Layout\Column::make([
                            \MoonShine\UI\Fields\Number::make('Subtotal Global', 'subtotal')
                                ->readonly()
                                ->prefix(get_currency_symbol())
                                ->step(0.01),
                        ])->columnSpan(3),

                        \MoonShine\UI\Components\Layout\Column::make([
                            \MoonShine\UI\Fields\Number::make('IVA (%)', 'impuesto_porcentaje')
                                ->fill($iva)
                                ->readonly(),
                        ])->columnSpan(2),

                        \MoonShine\UI\Components\Layout\Column::make([
                            \MoonShine\UI\Fields\Number::make('Monto IVA', 'impuesto_monto')
                                ->readonly()
                                ->prefix(get_currency_symbol())
                                ->step(0.01),
                        ])->columnSpan(3),

                        \MoonShine\UI\Components\Layout\Column::make([
                            \MoonShine\UI\Fields\Number::make('Total Factura', 'total')
                                ->readonly()
                                ->prefix(get_currency_symbol())
                                ->step(0.01)
                                ->customAttributes([
                                    'style' => 'font-weight: bold; font-size: 1.2em; color: rgba(var(--primary));'
                                ]),
                        ])->columnSpan(4),
                    ])
                ])
            ])->name('resumen-compra'),
        ];
    }

    protected function buttons(): ListOf
    {
        return new ListOf(\MoonShine\Contracts\UI\ActionButtonContract::class, [
            ...parent::buttons()->toArray(),
            \MoonShine\UI\Components\ActionButton::make(
                'Procesar e Ingresar a Inventario',
            )
            ->method('completarCompra')
            ->primary()
            ->icon('check-circle')
            ->canSee(fn (mixed $item) => $item->estado === 'borrador')
            ->withConfirm(
                '¿Confirmar Recepción?',
                'Esto incrementará el stock de los productos y actualizará los costos del sistema de forma permanente.',
                'Proceder'
            ),
        ]);
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
        $item = $this->getResource()->getItem();
        $isCompleted = $item && $item->estado === 'completada';

        if ($isCompleted) {
            return $component->hideSubmit();
        }

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
