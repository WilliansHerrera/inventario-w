<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $fillable = ['nombre', 'locale_id', 'saldo', 'abierta', 'sync_token'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($caja) {
            if (empty($caja->sync_token)) {
                $caja->sync_token = \Illuminate\Support\Str::random(32);
            }
        });
    }

    public function sucursal()
    {
        return $this->belongsTo(Locale::class, 'locale_id');
    }

    public function movimientos()
    {
        return $this->hasMany(CajaMovimiento::class);
    }
}
