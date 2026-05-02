<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventario_id',
        'user_id',
        'cantidad',
        'tipo',
        'motivo',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
