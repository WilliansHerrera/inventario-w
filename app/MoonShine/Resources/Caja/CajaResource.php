<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja;

use App\Models\Caja;
use App\MoonShine\Resources\Caja\Pages\CajaIndexPage;
use App\MoonShine\Resources\Caja\Pages\CajaFormPage;
use App\MoonShine\Resources\Caja\Pages\CajaDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\UI\Components\ActionButton;

/**
 * @extends ModelResource<Caja, CajaIndexPage, CajaFormPage, CajaDetailPage>
 */
class CajaResource extends ModelResource
{
    protected string $model = Caja::class;

    protected string $title = 'Cajas';
    
    protected string $column = 'nombre';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CajaIndexPage::class,
            CajaFormPage::class,
            CajaDetailPage::class,
        ];
    }

    public function fields(): array
    {
        return [
            \MoonShine\UI\Fields\ID::make()->sortable(),
            \MoonShine\UI\Fields\Text::make('Nombre'),
            \MoonShine\UI\Fields\Relationships\BelongsTo::make('Sucursal', 'sucursal', resource: \App\MoonShine\Resources\Locale\LocaleResource::class),
            \MoonShine\UI\Fields\Number::make('Saldo Actual', 'saldo')->sortable()->badge('success'),
            \MoonShine\UI\Fields\Text::make('Estado', 'abierta')
                ->changePreview(fn($value) => $value 
                    ? \MoonShine\UI\Components\Badge::make('Jornada Activa', 'success')
                    : \MoonShine\UI\Components\Badge::make('Caja Cerrada', 'gray')
                ),
            \MoonShine\UI\Fields\Switcher::make('Inc. Apertura Global', 'incluir_en_apertura_global')
                ->default(true),
        ];
    }

    public function rules($item): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'locale_id' => ['required', 'exists:locales,id'],
            'saldo' => ['required', 'numeric'],
            'incluir_en_apertura_global' => ['boolean'],
        ];
    }

    /**
     * @return list<ActionButton>
     */
    public function indexButtons(): array
    {
        return [
            // BOTÓN: ABRIR TURNO
            ActionButton::make('Abrir Jornada', fn(Caja $item) => route('admin.caja.abrir', $item))
                ->canSee(fn(Caja $item) => !$item->abierta)
                ->success()
                ->icon('play')
                ->withConfirm(
                    'Arqueo de Apertura',
                    'El sistema tiene asignado un fondo inicial de ' . format_currency(get_global_setting('default_opening_amount', 50)) . '. Por favor, introduce el efectivo REAL que tienes en caja para abrir.',
                    'Iniciar Jornada Auditada',
                    fn() => [
                        \MoonShine\UI\Fields\Number::make('Efectivo Físico Contado', 'monto_apertura')
                            ->default(get_global_setting('default_opening_amount', 50))
                            ->required(),
                        \MoonShine\UI\Fields\Hidden::make('monto_esperado')
                            ->default(get_global_setting('default_opening_amount', 50))
                    ]
                ),

            // BOTÓN: CERRAR TURNO (ARCHEO)
            ActionButton::make('Cerrar Jornada (Arqueo)', fn(Caja $item) => route('admin.caja.cerrar', $item))
                ->canSee(fn(Caja $item) => $item->abierta)
                ->warning()
                ->icon('stop')
                ->withConfirm(
                    'Cerrar Jornada',
                    'Ingresa el efectivo total contado físicamente en la caja (incluyendo el fondo inicial). El sistema auditará si hay descuadres.',
                    'Cerrar y Auditar',
                    fn() => [
                        \MoonShine\UI\Fields\Number::make('Efectivo Físico Contado', 'monto_real')
                            ->required()
                    ]
                ),

            // BOTÓN: REGISTRAR EGRESO (GASTO)
            ActionButton::make('Registrar Gasto', fn(Caja $item) => route('admin.caja.egreso', $item))
                ->primary()
                ->icon('minus-circle')
                ->withConfirm(
                    'Registrar Gasto de Caja',
                    'Registra una salida de dinero para proveedores, servicios u otros conceptos.',
                    'Guardar Egreso',
                    fn() => [
                        \MoonShine\UI\Fields\Number::make('Monto', 'monto')
                            ->required(),
                        \MoonShine\UI\Fields\Select::make('Categoría', 'categoria_id')
                            ->options(\App\Models\CajaMovimientoCategoria::where('tipo', 'egreso')->pluck('nombre', 'id')->toArray())
                            ->nullable(),
                        \MoonShine\UI\Fields\Text::make('Descripción / Otro Concepto', 'descripcion_libre')
                            ->placeholder('Ej: Pago de luz, limpieza, etc.')
                    ]
                ),
        ];
    }

    /**
     * @return list<ActionButton>
     */
    public function actions(): array
    {
        return [
            ActionButton::make('Iniciar Jornada Única (Toda la Tienda)', route('admin.caja.iniciar-dia'))
                ->primary()
                ->icon('sun')
                ->showInLine(),
        ];
    }
}
