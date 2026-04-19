<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    public $costo_actual_referencia;

    protected $fillable = [
        'compra_id',
        'producto_id',
        'cantidad',
        'costo_unitario',
        'subtotal'
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    protected static function booted()
    {
        static::saving(function ($detalle) {
            $detalle->subtotal = (float) $detalle->cantidad * (float) $detalle->costo_unitario;
        });

        static::saved(function ($detalle) {
            $detalle->compra?->recalculateTotals();
        });

        static::deleted(function ($detalle) {
            $detalle->compra?->recalculateTotals();
        });
    }
}
