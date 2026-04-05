<?php

namespace App\Services;

use App\Models\Inventario;
use Illuminate\Validation\ValidationException;

use App\Models\InventarioMovimiento;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    /**
     * Check if a product already has inventory in a specific locale.
     */
    public function checkDuplicate(int $productoId, int $localeId, ?int $excludeId = null): void
    {
        $query = Inventario::where('producto_id', $productoId)
            ->where('locale_id', $localeId);

        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'producto_id' => 'Este producto ya tiene un registro de inventario en este local.',
            ]);
        }
    }

    /**
     * Adjust stock for a product in a specific locale.
     */
    public function adjustStock(int $productoId, int $localeId, int $quantity, string $tipo = 'ajuste', string $motivo = 'Ajuste manual'): Inventario
    {
        $inventario = Inventario::firstOrCreate(
            ['producto_id' => $productoId, 'locale_id' => $localeId],
            ['stock' => 0]
        );

        $inventario->increment('stock', $quantity);

        // Registrar movimiento de auditoría
        InventarioMovimiento::create([
            'inventario_id' => $inventario->id,
            'user_id' => Auth::id() ?? 1, // Fallback a ID 1 si no hay sesión (ej. consola)
            'cantidad' => $quantity,
            'tipo' => $tipo,
            'motivo' => $motivo,
        ]);

        return $inventario;
    }
}
