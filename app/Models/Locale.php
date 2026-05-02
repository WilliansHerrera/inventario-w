<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    use HasFactory;

    protected $table = 'locales';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'sync_token',
    ];

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class);
    }
}
