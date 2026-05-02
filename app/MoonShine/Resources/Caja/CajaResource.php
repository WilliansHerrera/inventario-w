<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja;

use App\Models\Caja;
use App\Models\CajaMovimientoCategoria;
use App\MoonShine\Resources\Caja\Pages\CajaDetailPage;
use App\MoonShine\Resources\Caja\Pages\CajaFormPage;
use App\MoonShine\Resources\Caja\Pages\CajaIndexPage;
use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Caja, CajaIndexPage, CajaFormPage, CajaDetailPage>
 */
class CajaResource extends ModelResource
{
    protected string $model = Caja::class;

    protected string $title = 'Cajas';

    protected string $column = 'nombre';
    
    protected bool $columnSelection = true;

    public function search(): array
    {
        return ['id', 'nombre', 'sucursal.nombre'];
    }

    protected array $with = ['sucursal'];

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
            ID::make()->sortable(),
            Text::make(__('Nombre')),
            BelongsTo::make(__('Sucursal'), 'sucursal', resource: LocaleResource::class),
            Number::make(__('Saldo Actual'), 'saldo')->sortable()->badge('success'),
            Text::make(__('Efectivo en Turno'))
                ->changePreview(function ($value, Caja $item) {
                    if (! $item->abierta) {
                        return '-';
                    }
                    $summary = $item->getShiftSummary();

                    return format_currency($summary['esperado'] ?? 0);
                }),
            Text::make(__('Estado'), 'abierta')

                ->changePreview(fn ($value) => $value
                    ? Badge::make(__('Jornada Activa'), 'success')
                    : Badge::make(__('Caja Cerrada'), 'gray')
                ),
            Switcher::make(__('Inc. Apertura Global'), 'incluir_en_apertura_global')
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
            ActionButton::make(__('Abrir Jornada'), fn (Caja $item) => route('admin.caja.abrir', $item))
                ->canSee(fn (Caja $item) => ! $item->abierta)
                ->success()
                ->icon('play')
                ->withConfirm(
                    __('Arqueo de Apertura'),
                    __('El sistema tiene asignado un fondo inicial de ').format_currency(get_global_setting('default_opening_amount', 50)).__(' Por favor, introduce el efectivo REAL que tienes en caja para abrir.'),
                    __('Iniciar Jornada Auditada'),
                    fn () => [
                        Number::make(__('Efectivo Físico Contado'), 'monto_apertura')
                            ->default(get_global_setting('default_opening_amount', 50))
                            ->required(),
                        Hidden::make('monto_esperado')
                            ->default(get_global_setting('default_opening_amount', 50)),
                    ]
                ),

            // BOTÓN: CERRAR TURNO (ARCHEO)
            ActionButton::make(__('Cerrar Jornada (Arqueo)'), fn (Caja $item) => route('admin.caja.cerrar', $item))
                ->canSee(fn (Caja $item) => $item->abierta)
                ->warning()
                ->icon('stop')
                ->withConfirm(
                    fn (Caja $item) => __('Cerrar Jornada - Saldo Esperado: ').format_currency($item->getShiftSummary()['esperado'] ?? 0),

                    function (Caja $item) {
                        $summary = $item->getShiftSummary();
                        $ventas = format_currency($summary['ventas'] ?? 0);
                        $egresos = format_currency($summary['egresos'] ?? 0);
                        $fondo = format_currency($summary['apertura'] ?? 0);
                        $esperado = format_currency($summary['esperado'] ?? 0);

                        return "
                            <div class='mb-4 p-4 bg-slate-900 rounded-xl border border-slate-800 shadow-sm'>
                                <h4 class='text-xs font-bold uppercase tracking-wider text-slate-500 mb-3'>Estado de Caja Actual</h4>
                                <div class='space-y-2 text-sm'>
                                    <div class='flex justify-between items-center'>
                                        <span class='text-slate-400'>Ventas Registradas:</span>
                                        <span class='font-bold text-emerald-400'>+ $ventas</span>
                                    </div>
                                    <div class='flex justify-between items-center'>
                                        <span class='text-slate-400'>Egresos/Gastos:</span>
                                        <span class='font-bold text-rose-400'>- $egresos</span>
                                    </div>
                                    <div class='flex justify-between items-center'>
                                        <span class='text-slate-400'>Fondo de Apertura:</span>
                                        <span class='font-medium text-slate-300'>$fondo</span>
                                    </div>
                                    <div class='pt-2 mt-2 border-t border-slate-800 flex justify-between items-center'>
                                        <span class='font-bold text-slate-100 uppercase text-xs'>Total Esperado en Caja:</span>
                                        <span class='text-lg font-black text-green-500'>$esperado</span>
                                    </div>
                                </div>
                            </div>
                            <p class='text-sm text-slate-400 mb-2'>Por favor, cuenta el efectivo físico y escribe el monto total abajo:</p>
                        ";
                    },
                    __('Cerrar y Auditar'),

                    fn (Caja $item) => [
                        Number::make(__('Efectivo Físico Contado'), 'monto_real')
                            ->default($item->getShiftSummary()['esperado'] ?? 0)
                            ->hint(__('El sistema calcula que deberías tener: ').format_currency($item->getShiftSummary()['esperado'] ?? 0))
                            ->required(),
                    ]
                ),

            // BOTÓN: REGISTRAR EGRESO (GASTO)
            ActionButton::make(__('Registrar Gasto'), fn (Caja $item) => route('admin.caja.egreso', $item))
                ->primary()
                ->icon('minus-circle')
                ->withConfirm(
                    __('Registrar Gasto de Caja'),
                    __('Registra una salida de dinero para proveedores, servicios u otros conceptos.'),
                    __('Guardar Egreso'),
                    fn () => [
                        Number::make(__('Monto'), 'monto')
                            ->required(),
                        Select::make(__('Categoría'), 'categoria_id')
                            ->options(CajaMovimientoCategoria::where('tipo', 'egreso')->pluck('nombre', 'id')->toArray())
                            ->nullable(),
                        Text::make(__('Descripción / Otro Concepto'), 'descripcion_libre')
                            ->placeholder(__('Ej: Pago de luz, limpieza, etc.')),
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
            ActionButton::make(__('Iniciar Jornada Única (Toda la Tienda)'), route('admin.caja.iniciar-dia'))
                ->primary()
                ->icon('sun')
                ->showInLine(),
        ];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
