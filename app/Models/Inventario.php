<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $fillable = ['producto_id', 'locale_id', 'stock'];


    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class);
    }
}
