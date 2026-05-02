<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaMovimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'caja_id',
        'caja_turno_id',
        'user_id',
        'monto',
        'tipo',
        'descripcion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function turno()
    {
        return $this->belongsTo(CajaTurno::class, 'caja_turno_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
