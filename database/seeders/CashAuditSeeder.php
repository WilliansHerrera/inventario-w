<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CajaMovimientoCategoria;
use App\Models\GlobalSetting;

class CashAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categorías por defecto
        $categorias = [
            ['nombre' => 'Pago de Proveedores', 'tipo' => 'egreso', 'es_sistema' => true],
            ['nombre' => 'Compra de Suministros', 'tipo' => 'egreso', 'es_sistema' => true],
            ['nombre' => 'Pago de Servicios', 'tipo' => 'egreso', 'es_sistema' => true],
            ['nombre' => 'Adelanto de Sueldo', 'tipo' => 'egreso', 'es_sistema' => true],
            ['nombre' => 'Retiro de Dueño', 'tipo' => 'egreso', 'es_sistema' => false],
            ['nombre' => 'Aporte de Capital', 'tipo' => 'ingreso', 'es_sistema' => false],
            ['nombre' => 'Otros', 'tipo' => 'egreso', 'es_sistema' => true],
        ];

        foreach ($categorias as $cat) {
            CajaMovimientoCategoria::updateOrCreate(
                ['nombre' => $cat['nombre']],
                $cat
            );
        }

        // 2. Configuración Global (Solo inicializar si falta)
        $setting = GlobalSetting::first();
        if ($setting && !isset($setting->pos_block_without_shift)) {
             $setting->update(['pos_block_without_shift' => false]);
        }
    }
}
