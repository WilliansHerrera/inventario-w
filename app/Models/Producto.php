<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    public ?int $compra_id_for_log = null;

    protected $fillable = [
        'nombre',
        'sku',
        'codigo_barra',
        'descripcion',
        'imagen',
        'galeria',
        'precio',
        'margen',
        'precio_venta',
    ];

    protected $casts = [
        'galeria' => 'array',
    ];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function costoHistorials()
    {
        return $this->hasMany(ProductoCostoHistorial::class);
    }

    protected static function booted()
    {
        static::updating(function ($producto) {
            if ($producto->isDirty('precio')) {
                $producto->costoHistorials()->create([
                    'costo_anterior' => $producto->getOriginal('precio'),
                    'costo_nuevo' => $producto->precio,
                    'user_id' => auth()->id() ?? \App\Models\User::first()?->id,
                    'compra_id' => $producto->compra_id_for_log ?? request()->get('compra_id'),
                ]);
            }
        });
    }
}
