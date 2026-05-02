<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto_id',
        'locale_id',
        'stock',
    ];

    protected $casts = [
        'stock' => 'decimal:2',
    ];

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

    public ?string $movement_type = null;
    public ?string $movement_reason = null;

    protected static function booted()
    {
        static::updating(function ($inventario) {
            if ($inventario->isDirty('stock')) {
                $diff = $inventario->stock - $inventario->getOriginal('stock');
                
                $inventario->movimientos()->create([
                    'cantidad' => $diff,
                    'tipo' => $inventario->movement_type ?? ($diff > 0 ? 'ajuste_entrada' : 'ajuste_salida'),
                    'motivo' => $inventario->movement_reason ?? 'Ajuste manual de stock',
                    'user_id' => auth()->id() ?? 1
                ]);
            }
        });

        static::created(function ($inventario) {
            if ($inventario->stock > 0) {
                $inventario->movimientos()->create([
                    'cantidad' => $inventario->stock,
                    'tipo' => $inventario->movement_type ?? 'inicial',
                    'motivo' => $inventario->movement_reason ?? 'Stock inicial',
                    'user_id' => auth()->id() ?? 1
                ]);
            }
        });
    }
}
