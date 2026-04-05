<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = ['caja_id', 'user_id', 'total', 'metodo_pago'];

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
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
