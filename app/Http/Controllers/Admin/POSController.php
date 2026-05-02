<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\CashRegisterService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    public function __construct(
        protected CashRegisterService $cashService,
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Search products available in the locale of the selected cash register.
     * Stock is filtered by locale_id of the Caja.
     */
    public function search(Request $request)
    {
        $cajaId = (int) $request->get('caja_id');
        $query = trim($request->get('query', ''));

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

        // --- QUERY PRODUCTOS ---
        // Buscamos en el catálogo general de productos.
        $results = Producto::select(['id', 'nombre', 'sku', 'codigo_barra', 'precio_venta', 'precio', 'imagen'])
            ->with([
                'inventarios' => function ($q) {
                    $q->select(['id', 'producto_id', 'locale_id', 'stock']);
                },
                'inventarios.locale' => function ($q) {
                    $q->select(['id', 'nombre']);
                }
            ])
            ->where(function ($q) use ($query) {
                if ($query !== '') {
                    $q->where('nombre', 'like', "%{$query}%")
                        ->orWhere('sku', 'like', "%{$query}%")
                        ->orWhere('codigo_barra', 'like', "%{$query}%");
                }
            })
            ->limit(50)
            ->get()
            ->map(function ($producto) use ($caja) {
                $localInv = $producto->inventarios->where('locale_id', $caja->locale_id)->first();
                
                return [
                    'id' => $producto->id,
                    'inventario_id' => $localInv?->id,
                    'nombre' => $producto->nombre,
                    'sku' => $producto->sku,
                    'codigo_barra' => $producto->codigo_barra,
                    'precio' => (float) ($producto->precio_venta ?? $producto->precio),
                    'stock' => (int) ($localInv?->stock ?? 0),
                    'imagen' => $producto->imagen,
                    'other_stocks' => $producto->inventarios
                        ->where('locale_id', '!=', $caja->locale_id)
                        ->where('stock', '>', 0)
                        ->map(fn ($oi) => [
                            'sucursal' => $oi->locale->nombre ?? '?',
                            'stock' => (int) $oi->stock,
                        ])->values(),
                ];
            });

        return response()->json($results);
    }

    /**
     * Process the sale atomically.
     * All steps happen inside DB::transaction() — if anything fails, nothing is saved.
     */
    public function store(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'metodo_pago' => 'required|in:efectivo,tarjeta',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio' => 'required|numeric|min:0',
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
                $total = 0;
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
                $userId = auth()->id() ?? 1;
                foreach ($request->items as $item) {
                    $total += $item['cantidad'] * $item['precio'];
                }

                $venta = Venta::create([
                    'caja_id' => $caja->id,
                    'user_id' => $userId,
                    'total' => $total,
                    'metodo_pago' => $request->metodo_pago,
                ]);

                // --- Persist each item, discount stock, log movement ---
                foreach ($request->items as $item) {
                    $subtotal = $item['cantidad'] * $item['precio'];

                    VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $subtotal,
                    ]);

                    // Discount stock through the service (auto-audits movement)
                    $this->inventoryService->adjustStock(
                        productoId: $item['id'],
                        localeId: $caja->locale_id,
                        quantity: -$item['cantidad'],
                        tipo: 'venta',
                        motivo: "Venta #{$venta->id}",
                    );
                }

                // --- Register cash inflow ---
                $this->cashService->registerMovement(
                    caja: $caja,
                    tipo: 'venta',
                    monto: $total,
                    descripcion: "Venta #{$venta->id} — {$request->metodo_pago}",
                );

                return $venta->id;
            });

            return response()->json([
                'success' => true,
                'venta_id' => $ventaId,
                'total' => Venta::find($ventaId)->total,
            ]);

        } catch (\Exception $e) {
            Log::error('POS Store error: '.$e->getMessage());

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

    /**
     * Download the latest POS installer.
     */
    public function download()
    {
        $version = \App\Models\PosVersion::where('is_latest', true)->first();

        if (! $version || ! $version->filename) {
            // Fallback to a static file if no dynamic version is available
            $path = public_path('downloads/POS-Setup.exe');
            if (file_exists($path)) {
                return response()->download($path);
            }

            abort(404, 'No hay versiones del POS disponibles para descargar actualmente.');
        }

        // If filename is an external URL (GitHub), redirect to it
        if (filter_var($version->filename, FILTER_VALIDATE_URL)) {
            return redirect($version->filename);
        }

        // If it is a local file in storage
        $path = storage_path('app/public/pos/'.$version->filename);
        if (file_exists($path)) {
            return response()->download($path);
        }

        abort(404, 'El archivo del instalador no se encuentra en el servidor.');
    }
}
