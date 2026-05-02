<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaTurno extends Model
{
    use HasFactory;

    protected $fillable = [
        'caja_id',
        'user_id',
        'monto_apertura_esperado',
        'monto_apertura_real',
        'diferencia_apertura',
        'monto_cierre_esperado',
        'monto_cierre_real',
        'diferencia',
        'abierto_at',
        'cerrado_at',
        'estado',
        'denominaciones_apertura',
        'denominaciones_cierre',
    ];

    protected $casts = [
        'abierto_at' => 'datetime',
        'cerrado_at' => 'datetime',
        'denominaciones_apertura' => 'array',
        'denominaciones_cierre' => 'array',
        'monto_apertura_esperado' => 'decimal:2',
        'monto_apertura_real' => 'decimal:2',
        'diferencia_apertura' => 'decimal:2',
        'monto_cierre_esperado' => 'decimal:2',
        'monto_cierre_real' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }
}
