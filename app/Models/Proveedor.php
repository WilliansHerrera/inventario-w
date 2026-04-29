<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = ['nombre', 'ruc_dni', 'email', 'telefono', 'direccion'];


    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}
