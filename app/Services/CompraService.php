<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\ProductoCostoHistorial;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function procesarCompra(Compra $compra)
    {
        if ($compra->estado === 'completada') {
            throw new \Exception("La compra ya se encuentra completada.");
        }

        DB::beginTransaction();

        try {
            foreach ($compra->detalles as $detalle) {
                // 1. Alimentar Inventario
                $inventario = Inventario::firstOrCreate(
                    [
                        'producto_id' => $detalle->producto_id,
                        'locale_id' => $compra->locale_id,
                    ],
                    ['stock' => 0]
                );

                $inventario->stock += $detalle->cantidad;
                $inventario->save();

                // 2. Registrar Movimiento
                InventarioMovimiento::create([
                    'inventario_id' => $inventario->id,
                    'user_id' => $compra->user_id,
                    'cantidad' => $detalle->cantidad,
                    'tipo' => 'compra',
                    'motivo' => 'Recepción de compra N° ' . ($compra->nro_documento ?: $compra->id)
                ]);

                // 3. Actualizar Costo del Producto y dejar Historial
                $producto = $detalle->producto;
                $costoAnterior = $producto->precio; // precio = costo base en catálogo
                
                if (floatval($costoAnterior) !== floatval($detalle->costo_unitario)) {
                    // Guardar Historial
                    ProductoCostoHistorial::create([
                        'producto_id' => $producto->id,
                        'compra_id' => $compra->id,
                        'user_id' => \MoonShine\Laravel\MoonShineAuth::getGuard()->id() ?? $compra->user_id,
                        'costo_anterior' => $costoAnterior,
                        'costo_nuevo' => $detalle->costo_unitario
                    ]);

                    // Actualizar costo base
                    $producto->precio = $detalle->costo_unitario;
                    $producto->save();
                }
            }

            // Marcar Compra como completada
            $compra->estado = 'completada';
            $compra->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
