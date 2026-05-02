<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'locale_id',
        'user_id',
        'nro_documento',
        'estado',
        'subtotal',
        'impuesto_porcentaje',
        'impuesto_monto',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'impuesto_porcentaje' => 'decimal:2',
        'impuesto_monto' => 'decimal:2',
        'total' => 'decimal:2',
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
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    /**
     * Recalcula los totales de la compra basándose en sus detalles.
     */
    public function recalculateTotals(): void
    {
        $taxIncluded = get_global_setting('prices_include_tax', false);
        $taxRate = $this->impuesto_porcentaje ?: get_global_setting('iva_porcentaje', 13.00);

        $subtotalSum = $this->detalles()->sum('subtotal');

        if ($taxIncluded) {
            $total = (float) $subtotalSum;
            $subtotalBase = $total / (1 + ($taxRate / 100));
            $taxAmount = $total - $subtotalBase;
        } else {
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
