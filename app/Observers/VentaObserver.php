<?php

namespace App\Observers;

use App\Models\Venta;
use App\Models\Caja;

class VentaObserver
{
    /**
     * Handle the Venta "created" event.
     */
    public function created(Venta $venta): void
    {
        // El saldo de la caja se actualiza dinámicamente desde VentaDetalleObserver
        // para manejar mayor precisión con los productos añadidos.
    }
}
