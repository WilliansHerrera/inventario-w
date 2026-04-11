<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['nombre', 'sku', 'codigo_barra', 'descripcion', 'precio', 'margen', 'precio_venta', 'imagen'];

    protected $casts = [
        'precio' => 'decimal:2',
        'margen' => 'decimal:2',
        'precio_venta' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $date = now()->format('ymd');
                $random = strtoupper(\Illuminate\Support\Str::random(4));
                $product->sku = "INV-{$date}-{$random}";
            }
        });
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
