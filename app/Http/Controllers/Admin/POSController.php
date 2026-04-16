<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\CashRegisterService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function __construct(
        protected CashRegisterService $cashService,
        protected InventoryService    $inventoryService,
    ) {}

    /**
     * Search products available in the locale of the selected cash register.
     * Stock is filtered by locale_id of the Caja.
     */
    public function search(Request $request)
    {
        $cajaId = (int) $request->get('caja_id');
        $query  = trim($request->get('query', ''));

        if (! $cajaId) {
            return response()->json(['error' => 'No se ha seleccionado una caja.'], 422);
        }

        $caja = Caja::find($cajaId);

        if (! $caja) {
            return response()->json(['error' => 'Caja no encontrada.'], 422);
        }

        // --- APERTURA AUTOMÁTICA (Si está activa) ---
        $this->cashService->ensureOpenShift($caja);

        // Validación de bloqueo por configuración global o estado de caja
        if (! $caja->abierta) {
            return response()->json(['error' => 'La caja está cerrada. Debes iniciar la jornada con el arqueo de apertura.'], 422);
        }

        $results = Inventario::with('producto')
            ->where('locale_id', $caja->locale_id)
            ->where('stock', '>', 0)
            ->whereHas('producto', function ($q) use ($query) {
                if ($query !== '') {
                    $q->where('nombre', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%")
                      ->orWhere('codigo_barra', 'like', "%{$query}%");
                }
            })
            ->limit(50)
            ->get()
            ->map(fn ($inv) => [
                'id'           => $inv->producto->id,
                'inventario_id'=> $inv->id,
                'nombre'       => $inv->producto->nombre,
                'sku'          => $inv->producto->sku,
                'codigo_barra' => $inv->producto->codigo_barra,
                'precio'       => (float) $inv->producto->precio_venta ?? (float) $inv->producto->precio,
                'stock'        => (int) $inv->stock,
                'imagen'       => $inv->producto->imagen,
            ]);

        return response()->json($results);
    }

    /**
     * Process the sale atomically.
     * All steps happen inside DB::transaction() — if anything fails, nothing is saved.
     */
    public function store(Request $request)
    {
        $request->validate([
            'caja_id'    => 'required|exists:cajas,id',
            'metodo_pago'=> 'required|in:efectivo,tarjeta',
            'items'      => 'required|array|min:1',
            'items.*.id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio'   => 'required|numeric|min:0',
        ]);

        $caja = Caja::findOrFail($request->caja_id);
        
        // --- APERTURA AUTOMÁTICA (Si está activa) ---
        $this->cashService->ensureOpenShift($caja);

        // Validación de bloqueo por configuración global
        $blockWithoutShift = get_global_setting('pos_block_without_shift', false);
        if ($blockWithoutShift && ! $caja->abierta) {
            return response()->json(['error' => 'No puedes procesar ventas sin una jornada abierta.'], 422);
        }

        try {
            $ventaId = DB::transaction(function () use ($request, $caja) {
                $total      = 0;
                $detallesBatch = [];

                // --- Validate stock for ALL items first ---
                foreach ($request->items as $item) {
                    $inventario = Inventario::where('producto_id', $item['id'])
                        ->where('locale_id', $caja->locale_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($inventario->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para el producto ID {$item['id']}. Disponible: {$inventario->stock}.");
                    }
                }

                // --- Create the Venta record ---
                $userId = auth('moonshine')->id() ?? 1;
                foreach ($request->items as $item) {
                    $total += $item['cantidad'] * $item['precio'];
                }

                $venta = Venta::create([
                    'caja_id'    => $caja->id,
                    'user_id'    => $userId,
                    'total'      => $total,
                    'metodo_pago'=> $request->metodo_pago,
                ]);

                // --- Persist each item, discount stock, log movement ---
                foreach ($request->items as $item) {
                    $subtotal = $item['cantidad'] * $item['precio'];

                    VentaDetalle::create([
                        'venta_id'       => $venta->id,
                        'producto_id'    => $item['id'],
                        'cantidad'       => $item['cantidad'],
                        'precio_unitario'=> $item['precio'],
                        'subtotal'       => $subtotal,
                    ]);

                    // Discount stock through the service (auto-audits movement)
                    $this->inventoryService->adjustStock(
                        productoId: $item['id'],
                        localeId:   $caja->locale_id,
                        quantity:   -$item['cantidad'],
                        tipo:       'venta',
                        motivo:     "Venta #{$venta->id}",
                    );
                }

                // --- Register cash inflow ---
                $this->cashService->registerMovement(
                    caja:       $caja,
                    tipo:       'venta',
                    monto:      $total,
                    descripcion: "Venta #{$venta->id} — {$request->metodo_pago}",
                );

                return $venta->id;
            });

            return response()->json([
                'success'  => true,
                'venta_id' => $ventaId,
                'total'    => Venta::find($ventaId)->total,
            ]);

        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error("POS Store error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Render a minimal print ticket.
     */
    public function ticket(int $id)
    {
        $venta = Venta::with(['detalles.producto', 'caja'])->findOrFail($id);
        return view('admin.ticket', compact('venta'));
    }
}
