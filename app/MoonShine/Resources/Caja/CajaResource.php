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
                    function(Caja $item) {
                        $summary = $item->getShiftSummary();
                        $ventas = format_currency($summary['ventas']);
                        $egresos = format_currency($summary['egresos']);
                        $fondo = format_currency($summary['apertura']);
                        $esperado = format_currency($summary['esperado']);
                        
                        return "
                            <div class='mb-4 p-4 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm'>
                                <h4 class='text-xs font-bold uppercase tracking-wider text-slate-500 mb-3'>Estado de Caja Actual</h4>
                                <div class='space-y-2 text-sm'>
                                    <div class='flex justify-between items-center'>
                                        <span class='text-slate-600 dark:text-slate-400'>Ventas Registradas:</span>
                                        <span class='font-bold text-emerald-600'>+ $ventas</span>
                                    </div>
                                    <div class='flex justify-between items-center'>
                                        <span class='text-slate-600 dark:text-slate-400'>Egresos/Gastos:</span>
                                        <span class='font-bold text-rose-600'>- $egresos</span>
                                    </div>
                                    <div class='flex justify-between items-center'>
                                        <span class='text-slate-600 dark:text-slate-400'>Fondo de Apertura:</span>
                                        <span class='font-medium text-slate-700 dark:text-slate-300'>$fondo</span>
                                    </div>
                                    <div class='pt-2 mt-2 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center'>
                                        <span class='font-bold text-slate-800 dark:text-slate-100 uppercase text-xs'>Total Esperado en Caja:</span>
                                        <span class='text-lg font-black text-primary'>$esperado</span>
                                    </div>
                                </div>
                            </div>
                            <p class='text-sm text-slate-500 mb-2'>Por favor, cuenta el efectivo físico y escribe el monto total abajo:</p>
                        ";
                    },
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
