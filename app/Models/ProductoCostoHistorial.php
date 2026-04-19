<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Models\MoonshineUser;

class ProductoCostoHistorial extends Model
{
    protected $fillable = [
        'producto_id',
        'compra_id',
        'user_id',
        'costo_anterior',
        'costo_nuevo'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function user()
    {
        return $this->belongsTo(MoonshineUser::class);
    }
}
