<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\CajaMovimientoCategoria;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use MoonShine\Laravel\MoonShineUI;
use MoonShine\Support\Enums\ToastType;

class CajaActionController extends Controller
{
    public function __construct(
        protected CashRegisterService $service
    ) {}

    public function abrirTurno(Request $request, Caja $caja)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'monto_esperado' => 'nullable|numeric|min:0'
        ]);

        try {
            $this->service->openShift(
                caja: $caja, 
                initialBalanceReal: (float) $request->monto_apertura,
                expectedBalance: $request->monto_esperado ? (float) $request->monto_esperado : null
            );

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Jornada iniciada con éxito.']);
            }

            return \MoonShine\Laravel\MoonShineJsonResponse::make()
                ->toast('Jornada iniciada con éxito.', ToastType::SUCCESS)
                ->redirect(route('moonshine.resource.page', ['resourceUri' => 'caja-resource', 'pageUri' => 'caja-index-page']));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return \MoonShine\Laravel\MoonShineJsonResponse::make()
                ->toast($e->getMessage(), ToastType::ERROR);
        }
    }

    public function cerrarTurno(Request $request, Caja $caja)
    {
        $request->validate(['monto_real' => 'required|numeric|min:0']);

        try {
            $this->service->closeShift($caja, (float) $request->monto_real);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Jornada cerrada con éxito.']);
            }

            return \MoonShine\Laravel\MoonShineJsonResponse::make()
                ->toast('Jornada cerrada con éxito.', ToastType::SUCCESS)
                ->redirect(route('moonshine.resource.page', ['resourceUri' => 'caja-resource', 'pageUri' => 'caja-index-page']));
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return \MoonShine\Laravel\MoonShineJsonResponse::make()
                ->toast($e->getMessage(), ToastType::ERROR);
        }
    }

    public function registrarEgreso(Request $request, Caja $caja)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'categoria_id' => 'nullable|exists:caja_movimiento_categorias,id',
            'descripcion_libre' => 'nullable|string|max:255',
        ]);

        try {
            if (!$caja->abierta && get_global_setting('pos_block_without_shift', false)) {
                 throw new \Exception("La caja está cerrada y el bloqueo está activo.");
            }

            $categoria = $request->categoria_id ? CajaMovimientoCategoria::find($request->categoria_id) : null;
            $descripcion = $request->descripcion_libre ?: ($categoria ? $categoria->nombre : 'Egreso manual');

            $this->service->registerMovement(
                caja: $caja,
                tipo: 'egreso',
                monto: -$request->monto,
                descripcion: $descripcion,
                categoria_id: $request->categoria_id
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Egreso registrado con éxito.']);
            }

            return \MoonShine\Laravel\MoonShineJsonResponse::make()
                ->toast('Egreso registrado.', ToastType::SUCCESS)
                ->redirect(route('moonshine.resource.page', ['resourceUri' => 'caja-resource', 'pageUri' => 'caja-index-page']));
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return \MoonShine\Laravel\MoonShineJsonResponse::make()
                ->toast($e->getMessage(), ToastType::ERROR);
        }
    }

    public function iniciarDiaCompleto(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("Intento de Iniciar Día Completo Auditado");
        try {
            $montoApertura = (float) $request->get('monto_apertura', get_global_setting('default_opening_amount', 50));
            
            $cajasCerradas = Caja::where('abierta', false)
                ->where('incluir_en_apertura_global', true)
                ->get();

            \Illuminate\Support\Facades\Log::info("Cajas encontradas para apertura global: " . $cajasCerradas->count());

            if ($cajasCerradas->isEmpty()) {
                throw new \Exception("No hay cajas cerradas que requieran apertura global.");
            }

            foreach ($cajasCerradas as $caja) {
                $this->service->openShift($caja, $montoApertura, $montoApertura);
            }

            MoonShineUI::toast("Se han iniciado las jornadas para " . $cajasCerradas->count() . " cajas con un fondo de " . format_currency($montoApertura), ToastType::SUCCESS);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error en Iniciar Día Completo: " . $e->getMessage());
            MoonShineUI::toast($e->getMessage(), ToastType::ERROR);
        }

        return back();
    }
}
