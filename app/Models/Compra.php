<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Models\MoonshineUser;

class Compra extends Model
{
    protected $fillable = [
        'proveedor_id',
        'locale_id',
        'user_id',
        'nro_documento',
        'estado',
        'subtotal',
        'impuesto_porcentaje',
        'impuesto_monto',
        'total'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function user()
    {
        return $this->belongsTo(MoonshineUser::class);
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    /**
     * Recalcula los totales de la compra basándose en sus detalles y la configuración global de impuestos.
     */
    public function recalculateTotals(): void
    {
        $taxIncluded = get_global_setting('prices_include_tax', false);
        $taxRate = $this->impuesto_porcentaje ?: get_global_setting('iva_porcentaje', 13.00);
        
        // Suma de los subtotales de cada línea (cantidad * costo_unitario)
        $subtotalSum = $this->detalles()->sum('subtotal');
        
        if ($taxIncluded) {
            // ESCENARIO A: Los costos unitarios YA incluyen IVA
            // El total de la factura es la suma directa de las líneas.
            $total = (float) $subtotalSum;
            $subtotalBase = $total / (1 + ($taxRate / 100));
            $taxAmount = $total - $subtotalBase;
        } else {
            // ESCENARIO B: Los costos unitarios son NETOS (Sin IVA)
            // El subtotal de la factura es la suma de las líneas, y el IVA se suma al final.
            $subtotalBase = (float) $subtotalSum;
            $taxAmount = ($subtotalBase * $taxRate) / 100;
            $total = $subtotalBase + $taxAmount;
        }

        $this->update([
            'subtotal' => round($subtotalBase, 2),
            'impuesto_monto' => round($taxAmount, 2),
            'total' => round($total, 2),
        ]);
    }
}
