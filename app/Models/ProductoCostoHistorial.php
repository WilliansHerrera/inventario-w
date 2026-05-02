<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoCostoHistorial extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'compra_id',
        'user_id',
        'costo_anterior',
        'costo_nuevo',
    ];

    protected $casts = [
        'costo_anterior' => 'decimal:2',
        'costo_nuevo' => 'decimal:2',
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
        return $this->belongsTo(User::class);
    }
}
