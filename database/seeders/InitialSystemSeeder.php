<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\GlobalSetting;
use App\Models\Locale;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class InitialSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Sucursal
        $sucursal = Locale::firstOrCreate([
            'nombre' => 'Casa Matriz',
        ], [
            'direccion' => 'Av. Central #101',
            'telefono' => '2200-0000',
        ]);

        // 2. Caja
        Caja::firstOrCreate([
            'nombre' => 'Caja Central',
        ], [
            'locale_id' => $sucursal->id,
            'saldo' => 0,
            'abierta' => false,
        ]);

        // 3. Configuración Global
        GlobalSetting::firstOrCreate([], [
            'country_name' => 'El Salvador',
            'locale' => 'es_SV',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'iva_porcentaje' => 13.00,
            'prices_include_tax' => false,
            'auto_open_shifts' => true,
            'default_opening_amount' => 50.00,
        ]);

        // 4. Proveedor genérico
        Proveedor::firstOrCreate([
            'nombre' => 'Proveedor General',
        ], [
            'ruc_dni' => '0000-000000-000-0',
            'email' => 'proveedor@example.com',
        ]);
    }
}
