<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $fillable = ['nombre', 'locale_id', 'saldo', 'abierta'];

    public function sucursal()
    {
        return $this->belongsTo(Locale::class, 'locale_id');
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function terminales()
    {
        return $this->hasMany(TerminalPos::class);
    }
}
