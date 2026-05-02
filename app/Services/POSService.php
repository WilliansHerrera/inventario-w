<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CashRegisterService $cashService
    ) {}

    /**
     * Process a new sale atomically.
     */
    public function processSale(array $data)
    {
        return DB::transaction(function () use ($data) {
            $caja = Caja::findOrFail($data['caja_id']);

            // 1. Ensure shift is open (or fail if not auto-open)
            if (! $caja->abierta) {
                $this->cashService->ensureOpenShift($caja);
                if (! $caja->abierta) {
                    throw new \Exception('No se puede vender en una caja cerrada.');
                }
            }

            // 2. Create the Venta record
            $venta = Venta::create([
                'caja_id' => $data['caja_id'],
                'user_id' => Auth::id() ?? 1,
                'total' => $data['total'],
                'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
            ]);

            // 3. Process items
            foreach ($data['items'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);

                // NOTE: Inventory stock is now automatically updated 
                // via the model observer in VentaDetalle.
            }

            // 5. Register cash movement in the register
            $this->cashService->registerMovement(
                caja: $caja,
                tipo: 'venta',
                monto: $venta->total,
                descripcion: "Venta #{$venta->id} — ".strtoupper($venta->metodo_pago)
            );

            return $venta;
        });
    }
}
