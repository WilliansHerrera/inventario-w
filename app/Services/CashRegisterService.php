<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CashRegisterService
{
    /**
     * Open a cash register shift (Turno).
     */
    public function openShift(\App\Models\Caja $caja, float $initialBalanceReal, ?float $expectedBalance = null, ?int $userId = null): void
    {
        $userId = $userId ?? auth('moonshine')->id() ?? \Illuminate\Support\Facades\Auth::id() ?? 1;
        $expectedBalance = $expectedBalance ?? $initialBalanceReal; // Si no se provee, se asume que es igual
        $diferencia = $initialBalanceReal - $expectedBalance;

        // 1. Crear el registro del Turno con auditoría de apertura
        $turno = \App\Models\CajaTurno::create([
            'caja_id' => $caja->id,
            'user_id' => $userId,
            'monto_apertura_esperado' => $expectedBalance,
            'monto_apertura_real' => $initialBalanceReal,
            'diferencia_apertura' => $diferencia,
            'abierto_at' => now(),
            'estado' => 'abierto',
        ]);

        // 2. Actualizar estado de la caja
        $caja->update([
            'abierta' => true,
            'saldo' => $initialBalanceReal, // Iniciamos con lo que realmente hay
            'turno_activo_id' => $turno->id,
        ]);

        // 3. Registrar movimiento de apertura vinculado al turno
        \App\Models\CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $userId,
            'caja_turno_id' => $turno->id,
            'tipo' => 'apertura',
            'monto' => $initialBalanceReal,
            'descripcion' => 'Apertura de jornada. Fondo real: ' . format_currency($initialBalanceReal) . 
                            ($diferencia != 0 ? ' (Diferencia: ' . format_currency($diferencia) . ')' : ''),
        ]);
    }

    /**
     * Close a cash register shift (Turno) with physical count (arqueo).
     */
    public function closeShift(\App\Models\Caja $caja, float $montoReal, ?int $userId = null): void
    {
        $turno = $caja->turnoActivo;
        if (!$turno) {
            throw new \Exception("No hay un turno activo para cerrar en esta caja.");
        }

        $userId = $userId ?? auth('moonshine')->id() ?? \Illuminate\Support\Facades\Auth::id() ?? 1;
        $montoEsperado = (float) $caja->saldo;
        $diferencia = $montoReal - $montoEsperado;

        // 1. Cerrar el registro del Turno con auditoría
        $turno->update([
            'monto_cierre_esperado' => $montoEsperado,
            'monto_cierre_real' => $montoReal,
            'diferencia' => $diferencia,
            'cerrado_at' => now(),
            'estado' => 'cerrado',
        ]);

        // 2. Registrar movimiento de cierre vinculado al turno
        \App\Models\CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $userId,
            'caja_turno_id' => $turno->id,
            'tipo' => 'cierre',
            'monto' => 0, // No altera el saldo, es solo informativo de arqueo
            'descripcion' => "Cierre de jornada. Diferencia detectada: " . format_currency($diferencia),
        ]);

        // 3. Actualizar estado de la caja
        $caja->update([
            'abierta' => false,
            'turno_activo_id' => null,
            'saldo' => $montoReal, // Ajustamos el saldo al real encontrado
        ]);
    }

    /**
     * Register a movement (sale, expense, etc.) in the cash register.
     */
    public function registerMovement(\App\Models\Caja $caja, string $tipo, float $monto, string $descripcion = '', ?int $userId = null, ?int $categoria_id = null): void
    {
        $caja->increment('saldo', $monto);

        \App\Models\CajaMovimiento::create([
            'caja_id' => $caja->id,
            'user_id' => $userId ?? auth('moonshine')->id() ?? \Illuminate\Support\Facades\Auth::id() ?? 1,
            'caja_turno_id' => $caja->turno_activo_id, // Note: Use the current active ID
            'categoria_id' => $categoria_id,
            'tipo' => $tipo,
            'monto' => $monto,
            'descripcion' => $descripcion,
        ]);
    }

    /**
     * Ensures a shift is open if auto_open_shifts is enabled.
     */
    public function ensureOpenShift(\App\Models\Caja $caja): void
    {
        if (!$caja->abierta && get_global_setting('auto_open_shifts', false)) {
            $montoInicio = (float) get_global_setting('default_opening_amount', 50.0);
            $this->openShift($caja, $montoInicio, $montoInicio);
        }
    }
}
