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
        $version = PosVersion::where('is_latest', true)->first();

        if (!$version) {
            return response()->json([], 204); // No update
        }

        $url = url('storage/' . $version->filename);

        return response()->json([
            'version'  => $version->version,
            'notes'    => $version->changelog,
            'pub_date' => $version->release_date ? $version->release_date->toRfc3339String() : $version->created_at->toRfc3339String(),
            'platforms' => [
                'windows-x86_64' => [
                    'signature' => '', // TODO: Implement signatures if needed
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
        $localeId = $request->get('locale_id');
        $cajaId   = $request->get('caja_id');
        $token    = $request->header('X-Sync-Token');

        if ($cajaId) {
            $caja = Caja::with('sucursal')->find($cajaId);
            if (!$caja || !$caja->sucursal || $caja->sucursal->sync_token !== $token) {
                return response()->json(['success' => false, 'error' => 'Token de sincronización inválido o caja no encontrada.'], 401);
            }
        }
        
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

        return response()->json([
            'success' => true,
            'data'    => $data,
            'count'   => $data->count()
        ]);
    }

    /**
     * Receive sales from local POS.
     * Expects an array of sales.
     */
    public function sales(Request $request)
    {
        $token = $request->header('X-Sync-Token');

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

                if (!$caja->sucursal || $caja->sucursal->sync_token !== $token) {
                    throw new \Exception("Token inválido para la caja {$caja->id}");
                }

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
}
