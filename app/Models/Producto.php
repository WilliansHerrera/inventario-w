<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['nombre', 'sku', 'codigo_barra', 'descripcion', 'precio', 'precio_venta', 'imagen'];

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
