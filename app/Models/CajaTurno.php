<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaTurno extends Model
{
    protected $table = 'caja_turnos';

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
        'denominaciones_cierre'
    ];

    protected $casts = [
        'abierto_at' => 'datetime',
        'cerrado_at' => 'datetime',
        'denominaciones_apertura' => 'array',
        'denominaciones_cierre' => 'array',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function user()
    {
        return $this->belongsTo(\MoonShine\Laravel\Models\MoonshineUser::class, 'user_id');
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class, 'caja_turno_id');
    }
}
