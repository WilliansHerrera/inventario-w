<?php

namespace App\Observers;

use App\Models\VentaDetalle;
use App\Models\Inventario;

class VentaDetalleObserver
{
    /**
     * Handle the VentaDetalle "creating" event.
     */
    public function creating(VentaDetalle $ventaDetalle): void
    {
        // Calcular subtotal antes de guardar
        $ventaDetalle->subtotal = $ventaDetalle->cantidad * $ventaDetalle->precio_unitario;
    }

    /**
     * Handle the VentaDetalle "created" event.
     */
    public function created(VentaDetalle $ventaDetalle): void
    {
        $venta = $ventaDetalle->venta;
        
        // 1. Actualizar total de la venta
        $venta->increment('total', $ventaDetalle->subtotal);

        $caja = $venta->caja;
        if ($caja) {
            // 2. Sumar al saldo de la caja
            $caja->increment('saldo', $ventaDetalle->subtotal);

            // 3. Descontar stock
            $inventario = Inventario::where('producto_id', $ventaDetalle->producto_id)
                ->where('locale_id', $caja->locale_id)
                ->first();

            if ($inventario) {
                $inventario->decrement('stock', $ventaDetalle->cantidad);
            } else {
                Inventario::create([
                    'producto_id' => $ventaDetalle->producto_id,
                    'locale_id' => $caja->locale_id,
                    'stock' => -($ventaDetalle->cantidad)
                ]);
            }
        }
    }

    /**
     * Handle the VentaDetalle "deleted" event.
     */
    public function deleted(VentaDetalle $ventaDetalle): void
    {
        $venta = $ventaDetalle->venta;
        
        // 1. Restar del total de la venta
        $venta->decrement('total', $ventaDetalle->subtotal);

        $caja = $venta->caja;
        if ($caja) {
            // 2. Restar del saldo de la caja
            $caja->decrement('saldo', $ventaDetalle->subtotal);

            // 3. Devolver stock
            $inventario = Inventario::where('producto_id', $ventaDetalle->producto_id)
                ->where('locale_id', $caja->locale_id)
                ->first();

            if ($inventario) {
                $inventario->increment('stock', $ventaDetalle->cantidad);
            }
        }
    }
}
