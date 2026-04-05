<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    protected $fillable = ['nombre', 'direccion', 'telefono'];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class);
    }

    public function __toString(): string
    {
        return (string) $this->nombre;
    }
}
