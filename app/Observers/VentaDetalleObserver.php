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
        // NOTA: La lógica de actualización de total, saldo de caja y stock
        // ha sido centralizada en POSController y los Servicios correspondientes
        // (InventoryService, CashRegisterService) para evitar duplicidad.
    }

    /**
     * Handle the VentaDetalle "deleted" event.
     */
    public function deleted(VentaDetalle $ventaDetalle): void
    {
        // NOTA: La lógica de actualización de total, saldo de caja y stock
        // ha sido centralizada en Servicios para mantener la integridad atómica.
    }
}
