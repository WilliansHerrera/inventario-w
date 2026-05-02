<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_id',
        'producto_id',
        'cantidad',
        'costo_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'costo_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
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

            // Update product cost
            if ($detalle->producto) {
                $detalle->producto->compra_id_for_log = $detalle->compra_id;
                $detalle->producto->update([
                    'precio' => $detalle->costo_unitario,
                ]);

                // Update inventory
                if ($detalle->compra?->locale_id) {
                    $inventario = Inventario::firstOrNew([
                        'producto_id' => $detalle->producto_id,
                        'locale_id' => $detalle->compra->locale_id
                    ]);

                    $inventario->movement_type = 'compra';
                    $inventario->movement_reason = 'Compra #' . ($detalle->compra->nro_documento ?? $detalle->compra->id);
                    $inventario->stock = ($inventario->stock ?? 0) + $detalle->cantidad;
                    $inventario->save();
                }
            }
        });

        static::deleted(function ($detalle) {
            $detalle->compra?->recalculateTotals();
        });
    }
}
