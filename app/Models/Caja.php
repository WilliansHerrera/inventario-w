<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $fillable = ['nombre', 'locale_id', 'saldo', 'abierta', 'incluir_en_apertura_global', 'apertura_automatica_pos', 'turno_activo_id'];

    protected $casts = [
        'abierta'                  => 'boolean',
        'incluir_en_apertura_global' => 'boolean',
        'apertura_automatica_pos'  => 'boolean',
        'saldo'                    => 'decimal:2',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Locale::class, 'locale_id');
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function terminales()
    {
        return $this->hasMany(TerminalPos::class);
    }

    public function turnos()
    {
        return $this->hasMany(CajaTurno::class);
    }

    public function turnoActivo()
    {
        return $this->belongsTo(CajaTurno::class, 'turno_activo_id');
    }

    public function getShiftSummary(): array
    {
        if (!$this->abierta || !$this->turno_activo_id) {
            return [
                'ventas' => 0,
                'ventas_efectivo' => 0,
                'ventas_tarjeta' => 0,
                'egresos' => 0,
                'apertura' => 0,
                'esperado' => (float)$this->saldo
            ];
        }

        $turno = $this->turnoActivo;
        $movimientos = $turno->movimientos;

        // Desglose de ventas por método de pago desde el modelo Venta
        $ventasDetail = Venta::where('caja_id', $this->id)
            ->where('created_at', '>=', $turno->abierto_at)
            ->select('metodo_pago', \Illuminate\Support\Facades\DB::raw('SUM(total) as total'))
            ->groupBy('metodo_pago')
            ->pluck('total', 'metodo_pago')
            ->toArray();

        return [
            'ventas'            => (float) $movimientos->where('tipo', 'venta')->sum('monto'),
            'ventas_efectivo'   => (float) ($ventasDetail['efectivo'] ?? 0),
            'ventas_tarjeta'    => (float) ($ventasDetail['tarjeta'] ?? 0),
            'egresos'           => (float) abs($movimientos->where('tipo', 'egreso')->sum('monto')),
            'apertura'          => (float) $movimientos->where('tipo', 'apertura')->sum('monto'),
            'esperado'          => (float) $this->saldo,
        ];
    }
}
