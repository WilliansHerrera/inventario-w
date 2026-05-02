<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\ProductoCostoHistorial;
use Illuminate\Support\Facades\DB;
use MoonShine\Laravel\MoonShineAuth;

class CompraService
{
    public function processPurchase(Compra $compra)
    {
        if ($compra->estado === 'completada') {
            throw new \Exception('La compra ya se encuentra completada.');
        }

        DB::beginTransaction();

        try {
            // NOTE: Product cost and Inventory stock are now automatically updated 
            // via the model observers in CompraDetalle, Producto, and Inventario models.
            // This service now only handles the high-level purchase completion state.

            // Ensure totals are correct one last time
            $compra->recalculateTotals();

            // Mark Compra as completed
            $compra->estado = 'completada';
            $compra->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
