<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaMovimiento extends Model
{
    protected $table = 'caja_movimientos';

    protected $fillable = ['caja_id', 'user_id', 'caja_turno_id', 'categoria_id', 'tipo', 'monto', 'descripcion'];

    public function categoria()
    {
        return $this->belongsTo(CajaMovimientoCategoria::class, 'categoria_id');
    }

    public function turno()
    {
        return $this->belongsTo(CajaTurno::class, 'caja_turno_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function user()
    {
        return $this->belongsTo(\MoonShine\Laravel\Models\MoonshineUser::class, 'user_id');
    }
}
