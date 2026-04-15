<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Contracts\UI\FieldContract;
use App\MoonShine\Resources\Caja\CajaResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;

/**
 * @extends IndexPage<CajaResource>
 */
class CajaIndexPage extends IndexPage
{
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Nombre', 'nombre')->sortable()->required(),
            BelongsTo::make('Sucursal', 'sucursal', resource: LocaleResource::class)->sortable(),
            Number::make('Saldo', 'saldo')
                ->sortable()
                ->changePreview(fn($value) => format_currency((float) $value)),
            \MoonShine\UI\Fields\Text::make('Estado', 'abierta')
                ->changePreview(fn($value) => $value 
                    ? \MoonShine\UI\Components\Badge::make('En Jornada', 'success')
                    : \MoonShine\UI\Components\Badge::make('Cerrada', 'gray')
                ),
            \MoonShine\UI\Fields\Switcher::make('Inc. Apertura Global', 'incluir_en_apertura_global')->sortable(),
        ];
    }

    protected function buttons(): \MoonShine\Support\ListOf
    {
        return new \MoonShine\Support\ListOf(\MoonShine\Contracts\UI\ActionButtonContract::class, [
            // Botones Estándar (Editar / Borrar)
            \MoonShine\UI\Components\ActionButton::make('', fn($item) => $this->getResource()->getFormPageUrl($item->getKey()))
                ->primary()->icon('pencil')
                ->showInLine(),

            \MoonShine\UI\Components\ActionButton::make('', fn($item) => $this->getResource()->getRoute('crud.destroy', $item->getKey()))
                ->error()->icon('trash')
                ->withConfirm('Confirmar eliminación', '¿Estás seguro de que deseas eliminar esta caja?')
                ->showInLine(),

            // Botones de Auditoría
             \MoonShine\UI\Components\ActionButton::make('Abrir', fn($item) => route('admin.caja.abrir', $item))
                ->canSee(fn($item) => !$item->abierta)
                ->success()->icon('play')
                ->withConfirm(
                    'Arqueo de Apertura',
                    'Introduce el efectivo REAL en caja para abrir.',
                    'Abrir',
                    fn() => [\MoonShine\UI\Fields\Number::make('Efectivo Físico', 'monto_apertura')->required()]
                )
                ->showInLine(),

            \MoonShine\UI\Components\ActionButton::make('Cerrar', fn($item) => route('admin.caja.cerrar', $item))
                ->canSee(fn($item) => $item->abierta)
                ->warning()->icon('stop')
                ->withConfirm(
                    'Cerrar Jornada',
                    'Introduce el efectivo total contado.',
                    'Cerrar',
                    fn() => [\MoonShine\UI\Fields\Number::make('Efectivo Real', 'monto_real')->required()]
                )
                ->showInLine(),

            \MoonShine\UI\Components\ActionButton::make('Gasto', fn($item) => route('admin.caja.egreso', $item))
                ->primary()->icon('minus-circle')
                ->withConfirm(
                    'Nuevo Gasto',
                    'Registra una salida de dinero.',
                    'Guardar',
                    fn() => [
                        \MoonShine\UI\Fields\Number::make('Monto', 'monto')->required(),
                        \MoonShine\UI\Fields\Text::make('Descripción', 'descripcion_libre')
                    ]
                )
                ->showInLine(),
        ]);
    }

    protected function topLayer(): array
    {
        return [
            \MoonShine\UI\Components\ActionGroup::make([
                \MoonShine\UI\Components\ActionButton::make(
                    'Añadir Caja', 
                    fn() => $this->getResource()->getFormPageUrl()
                )
                ->success()
                ->icon('plus')
                ->class('mb-4'),

                \MoonShine\UI\Components\ActionButton::make(
                    'Iniciar Jornada Única (Toda la Tienda)', 
                    fn() => route('admin.caja.iniciar-dia')
                )
                ->primary()
                ->icon('sun')
                ->class('mb-4')
                ->withConfirm(
                    'Apertura Global Auditada',
                    'Ingresa el monto de apertura para TODAS las cajas seleccionadas.',
                    'Iniciar Todo',
                    fn() => [
                        \MoonShine\UI\Fields\Number::make('Monto de Apertura Estándar', 'monto_apertura')
                            ->default(get_global_setting('default_opening_amount', 50))
                            ->required()
                    ]
                ),
            ]),
        ];
    }
}
