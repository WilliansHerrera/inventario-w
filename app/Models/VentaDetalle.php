<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    protected static function booted()
    {
        static::saved(function ($detalle) {
            $localeId = $detalle->venta?->caja?->locale_id;

            if ($localeId) {
                $inventario = Inventario::where('producto_id', $detalle->producto_id)
                    ->where('locale_id', $localeId)
                    ->first();

                if ($inventario) {
                    $inventario->movement_type = 'venta';
                    $inventario->movement_reason = 'Venta #' . $detalle->venta_id;
                    $inventario->stock -= $detalle->cantidad;
                    $inventario->save();
                }
            }
        });
    }
}
