<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CajaMovimientoCategoria extends Model
{
    protected $table = 'caja_movimiento_categorias';

    protected $fillable = ['nombre', 'tipo', 'es_sistema'];

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class, 'categoria_id');
    }
}
