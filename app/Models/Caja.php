<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $fillable = ['nombre', 'locale_id', 'saldo', 'abierta', 'incluir_en_apertura_global', 'apertura_automatica_pos', 'turno_activo_id'];

    protected $casts = [
        'abierta'                  => 'boolean',
        'incluir_en_apertura_global' => 'boolean',
        'apertura_automatica_pos'  => 'boolean',
        'saldo'                    => 'decimal:2',
    ];

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

    public function turnos()
    {
        return $this->hasMany(CajaTurno::class);
    }

    public function turnoActivo()
    {
        return $this->belongsTo(CajaTurno::class, 'turno_activo_id');
    }
}
