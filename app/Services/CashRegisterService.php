<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CashRegisterService
{
    /**
     * Open a cash register.
     */
    public function open(Caja $caja, float $initialBalance, ?int $userId = null): void
    {
        $caja->update([
            'abierta' => true,
            'saldo' => $initialBalance,
        ]);

        CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $userId ?? Auth::id() ?? 1,
            'tipo' => 'apertura',
            'monto' => $initialBalance,
            'descripcion' => 'Apertura de caja',
        ]);
    }

    /**
     * Close a cash register.
     */
    public function close(Caja $caja, float $finalBalance, ?int $userId = null): void
    {
        $caja->update([
            'abierta' => false,
            'saldo' => $finalBalance,
        ]);

        CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $userId ?? Auth::id() ?? 1,
            'tipo' => 'cierre',
            'monto' => $finalBalance,
            'descripcion' => 'Cierre de caja',
        ]);
    }

    /**
     * Register a movement (sale, expense, etc.) in the cash register.
     */
    public function registerMovement(Caja $caja, string $tipo, float $monto, string $descripcion = '', ?int $userId = null): void
    {
        $caja->increment('saldo', $monto);

        CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $userId ?? Auth::id() ?? 1,
            'tipo' => $tipo,
            'monto' => $monto,
            'descripcion' => $descripcion,
        ]);
    }
}
