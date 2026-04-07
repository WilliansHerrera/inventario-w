<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    protected $fillable = ['nombre', 'direccion', 'telefono', 'sync_token'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($locale) {
            if (empty($locale->sync_token)) {
                $locale->sync_token = \Illuminate\Support\Str::random(32);
            }
        });
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class);
    }

    public function getConfigArray(): array
    {
        $global = \App\Models\GlobalSetting::first();

        // Obtener cajas de esta sucursal
        $cajasArray = $this->cajas->map(function($caja) {
            return [
                'id' => $caja->id,
                'nombre' => $caja->nombre,
                'abierta' => (bool)$caja->abierta,
            ];
        })->toArray();

        return [
            'app_name' => 'Inventario-W POS',
            'api_url' => url('/api/v1'),
            'sucursal' => [
                'id' => $this->id,
                'nombre' => $this->nombre,
                'sync_token' => $this->sync_token,
            ],
            'settings' => [
                'kiosk_mode' => (bool)($global->win_kiosk_mode ?? false),
                'debug_mode' => (bool)($global->win_debug_mode ?? false),
                'sync_interval' => (int)($global->win_sync_interval ?? 60),
                'auto_actualizar' => (bool)($global->win_auto_actualizar ?? true),
                'auto_inicio' => (bool)($global->win_auto_inicio ?? true),
                'version_requerida' => ($global->win_min_version ?? '1.0.0'),
                'ruta_datos' => ($global->win_default_ruta_datos ?? 'C:\POS\Data'),
            ],
            'cajas' => $cajasArray,
        ];
    }

    public function getConfigJson(): string
    {
        return json_encode($this->getConfigArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function __toString(): string

    {
        return (string) $this->nombre;
    }
}
