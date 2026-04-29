<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Inventario;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Producto;
use App\Models\PosVersion;
use App\Services\InventoryService;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CashRegisterService $cashService
    ) {}

    /**
     * Check for POS updates (Tauri format)
     */
    public function checkUpdate(Request $request)
    {
        $version = PosVersion::where('is_latest', true)->orderBy('created_at', 'desc')->first();

        if (!$version) {
            return response()->json([], 204); // No update content
        }

        // Si el filename ya es una URL (GitHub), la usamos directamente
        $url = filter_var($version->filename, FILTER_VALIDATE_URL) 
            ? $version->filename 
            : url('storage/' . $version->filename);

        return response()->json([
            'version'  => $version->version,
            'notes'    => $version->changelog,
            'pub_date' => $version->release_date ? $version->release_date->toRfc3339String() : $version->created_at->toRfc3339String(),
            'platforms' => [
                'windows-x86_64' => [
                    'signature' => $version->signature ?? '=== DUMMY_TRIAL_SIGNATURE_V2.0.0 ===', // Dummy for testing as requested
                    'url'       => $url,
                ],
                'linux-x86_64' => [
                    'signature' => '',
                    'url'       => $url,
                ],
                'darwin-x86_64' => [
                    'signature' => '',
                    'url'       => $url,
                ]
            ]
        ]);
    }

    /**
     * Get products for local sync.
     * Optionally filtered by locale_id.
     */
    public function products(Request $request)
    {
        $localeId = $request->header('X-Sync-ID');
        
        $query = Inventario::with('producto');
        
        if ($localeId) {
            $query->where('locale_id', $localeId);
        }

        $data = $query->get()->map(function ($inv) {
            return [
                'id'            => $inv->producto->id,
                'nombre'        => $inv->producto->nombre,
                'sku'           => $inv->producto->sku,
                'codigo_barra'  => $inv->producto->codigo_barra,
                'precio_venta'  => (float) ($inv->producto->precio_venta ?? $inv->producto->precio),
                'stock'         => (int) $inv->stock,
                'imagen'        => $inv->producto->imagen,
                'locale_id'     => $inv->locale_id,
            ];
        });

        $settings = [];
        $cajas = [];
        $localeNombre = 'Establecimiento General';
        if ($localeId) {
            $locale = \App\Models\Locale::find($localeId);
            if ($locale) {
                $config = $locale->getConfigArray();
                $settings = $config['settings'] ?? [];
                $cajas = $config['cajas'] ?? [];
                $localeNombre = $locale->nombre;
            }
        }

        $users = \MoonShine\Laravel\Models\MoonshineUser::select('id', 'name', 'pos_pin')->get();


        return response()->json([
            'success' => true,
            'data'    => $data,
            'count'   => $data->count(),
            'settings'=> $settings,
            'cajas'   => $cajas,
            'users'   => $users,
            'locale_nombre' => $localeNombre
        ]);

    }

    /**
     * Receive sales from local POS.
     * Expects an array of sales.
     */
    public function sales(Request $request)
    {
        $request->validate([
            'sales' => 'required|array',
            'sales.*.local_uuid'  => 'required',
            'sales.*.caja_id'     => 'required|exists:cajas,id',
            'sales.*.user_id'     => 'nullable',
            'sales.*.metodo_pago' => 'required|in:efectivo,tarjeta',
            'sales.*.total'       => 'required|numeric',
            'sales.*.items'       => 'required|array|min:1',
            'sales.*.items.*.id'  => 'required|exists:productos,id',
            'sales.*.items.*.qty' => 'required|numeric|min:0.01',
            'sales.*.items.*.price' => 'required|numeric|min:0',
        ]);

        $syncedIds = [];
        $errors = [];

        foreach ($request->sales as $saleData) {
            try {
                $caja = Caja::with('sucursal')->findOrFail($saleData['caja_id']);

                DB::transaction(function () use ($saleData, $caja, &$syncedIds) {
                    // 1. Create Sale
                    $venta = Venta::create([
                        'caja_id'     => $caja->id,
                        'user_id'     => $saleData['user_id'] ?? 1,
                        'total'       => $saleData['total'],
                        'metodo_pago' => $saleData['metodo_pago'],
                        'created_at'  => $saleData['created_at'] ?? now(), // Mantener fecha original de la venta offline
                    ]);

                    // 2. Create Details & Adjust Stock
                    foreach ($saleData['items'] as $item) {
                        VentaDetalle::create([
                            'venta_id'       => $venta->id,
                            'producto_id'    => $item['id'],
                            'cantidad'       => $item['qty'],
                            'precio_unitario'=> $item['price'],
                            'subtotal'       => $item['qty'] * $item['price'],
                        ]);

                        $this->inventoryService->adjustStock(
                            productoId: $item['id'],
                            localeId:   $caja->locale_id,
                            quantity:   -$item['qty'],
                            tipo:       'venta_offline',
                            motivo:     "Sincronización Venta Offline #{$venta->id}",
                        );
                    }

                    // 3. Register Cash Flow
                    $this->cashService->registerMovement(
                        caja:        $caja,
                        tipo:        'venta',
                        monto:       $saleData['total'],
                        descripcion: "Sincronización Venta Offline #{$venta->id} — {$saleData['metodo_pago']}",
                    );

                    $syncedIds[] = $saleData['local_uuid'];
                });
            } catch (\Exception $e) {
                Log::error("Sync Error for Sale {$saleData['local_uuid']}: " . $e->getMessage());
                $errors[] = [
                    'local_uuid' => $saleData['local_uuid'],
                    'error'      => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success'    => count($errors) === 0,
            'synced_ids' => $syncedIds,
            'errors'     => $errors
        ]);
    }

    /**
     * Get the current status of the shift for a Caja.
     */
    public function shiftStatus(Request $request)
    {
        $cajaId = $request->query('caja_id');
        if (!$cajaId) {
            return response()->json(['error' => 'caja_id is required'], 400);
        }

        $caja = Caja::find($cajaId);
        if (!$caja) {
            return response()->json(['error' => 'Caja no encontrada'], 404);
        }

        return response()->json([
            'success' => true,
            'abierta' => (bool)$caja->abierta,
            'nombre'  => $caja->nombre,
        ]);
    }

    /**
     * Open a shift from the POS terminal.
     */
    public function shiftOpen(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'monto_apertura' => 'required|numeric|min:0',
        ]);

        $caja = Caja::findOrFail($request->caja_id);
        
        if ($caja->abierta) {
            return response()->json(['error' => 'La caja ya está abierta'], 400);
        }

        try {
            DB::transaction(function () use ($caja, $request) {
                $this->cashService->openShift(
                    caja: $caja,
                    initialBalanceReal: (float)$request->monto_apertura,
                    expectedBalance: (float)$request->monto_apertura
                );
            });
            return response()->json(['success' => true, 'abierta' => true]);
        } catch (\Exception $e) {
            Log::error("Error opening shift from POS: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Close a shift from the POS terminal.
     */
    public function shiftClose(Request $request)
    {
        $request->validate([
            'caja_id' => 'required|exists:cajas,id',
            'monto_cierre' => 'required|numeric|min:0',
            'denominaciones' => 'nullable|array'
        ]);

        $caja = Caja::findOrFail($request->caja_id);

        if (!$caja->abierta) {
            return response()->json(['error' => 'La caja ya está cerrada'], 400);
        }

        try {
            DB::transaction(function () use ($caja, $request) {
                $this->cashService->closeShift(
                    caja: $caja,
                    montoReal: (float)$request->monto_cierre,
                    userId: null,
                    denominaciones: $request->denominaciones ?? []
                );
            });
            return response()->json(['success' => true, 'abierta' => false]);
        } catch (\Exception $e) {
            Log::error("Error closing shift from POS: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the latest visual template for POS Offline.
     */
    public function template(Request $request)
    {
        $path = storage_path('app/public/pos/index.html');
        
        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'error' => 'No hay plantilla offline disponible en el servidor.'
            ], 404);
        }

        $html = file_get_contents($path);

        return response()->json([
            'success' => true,
            'html'    => $html,
            'updated_at' => filemtime($path)
        ]);
    }
}

