<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja\Pages;

use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\ActionGroup;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends IndexPage<CajaResource>
 */
class CajaIndexPage extends IndexPage
{
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable()->columnSelection(false),
            Text::make('Nombre', 'nombre')->sortable()->required()->columnSelection(false),
            BelongsTo::make('Sucursal', 'sucursal', resource: LocaleResource::class)->sortable(),
            Number::make('Saldo', 'saldo')
                ->sortable()
                ->changePreview(fn ($value) => format_currency((float) $value)),
            Text::make('Estado', 'abierta')
                ->changePreview(fn ($value) => $value
                    ? Badge::make('En Jornada', 'success')
                    : Badge::make('Cerrada', 'gray')
                ),
            Switcher::make('Inc. Apertura Global', 'incluir_en_apertura_global')->sortable(),
        ];
    }

    protected function modifyListComponent(ComponentContract $component): TableBuilder
    {
        return $component->columnSelection();
    }

    protected function buttons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, [
            // Botones Estándar (Editar / Borrar)
            ActionButton::make('', fn ($item) => $this->getResource()->getFormPageUrl($item->getKey()))
                ->primary()->icon('pencil')
                ->showInLine(),

            ActionButton::make('', fn ($item) => $this->getResource()->getRoute('crud.destroy', $item->getKey()))
                ->error()->icon('trash')
                ->withConfirm('Confirmar eliminación', '¿Estás seguro de que deseas eliminar esta caja?')
                ->showInLine(),

            // Botones de Auditoría
            ActionButton::make('Abrir', fn ($item) => route('admin.caja.abrir', $item))
                ->canSee(fn ($item) => ! $item->abierta)
                ->success()->icon('play')
                ->withConfirm(
                    'Arqueo de Apertura',
                    'Introduce el efectivo REAL en caja para abrir.',
                    'Abrir',
                    fn () => [Number::make('Efectivo Físico', 'monto_apertura')->required()]
                )
                ->showInLine(),

            ActionButton::make('Cerrar', fn ($item) => route('admin.caja.cerrar', $item))
                ->canSee(fn ($item) => $item->abierta)
                ->warning()->icon('stop')
                ->withConfirm(
                    'Cerrar Jornada',
                    'Introduce el efectivo total contado.',
                    'Cerrar',
                    fn () => [Number::make('Efectivo Real', 'monto_real')->required()]
                )
                ->showInLine(),

            ActionButton::make('Gasto', fn ($item) => route('admin.caja.egreso', $item))
                ->primary()->icon('minus-circle')
                ->withConfirm(
                    'Nuevo Gasto',
                    'Registra una salida de dinero.',
                    'Guardar',
                    fn () => [
                        Number::make('Monto', 'monto')->required(),
                        Text::make('Descripción', 'descripcion_libre'),
                    ]
                )
                ->showInLine(),
        ]);
    }

    protected function topLayer(): array
    {
        return [
            ActionGroup::make([
                ActionButton::make(
                    'Añadir Caja',
                    fn () => $this->getResource()->getFormPageUrl()
                )
                    ->success()
                    ->icon('plus')
                    ->class('mb-4'),

                ActionButton::make(
                    'Iniciar Jornada Única (Toda la Tienda)',
                    fn () => route('admin.caja.iniciar-dia')
                )
                    ->primary()
                    ->icon('sun')
                    ->class('mb-4')
                    ->withConfirm(
                        'Apertura Global Auditada',
                        'Ingresa el monto de apertura para TODAS las cajas seleccionadas.',
                        'Iniciar Todo',
                        fn () => [
                            Number::make('Monto de Apertura Estándar', 'monto_apertura')
                                ->default(get_global_setting('default_opening_amount', 50))
                                ->required(),
                        ]
                    ),
            ]),
        ];
    }
}
