<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Caja;
use App\Models\Locale;
use App\Models\Inventario;
use Faker\Factory as Faker;

class ExamplesDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // 1. Asegurar que existe un Local
        $local = Locale::first();
        if (!$local) {
            $local = Locale::create([
                'nombre' => 'Sucursal Central',
                'direccion' => 'Av. Principal 123',
                'telefono' => '555-0101'
            ]);
        }

        // 2. Crear 100 productos varios
        $categorias = ['Electrónicos', 'Papelería', 'Limpieza', 'Alimentos', 'Ropa'];
        
        for ($i = 1; $i <= 100; $i++) {
            $nombre = $faker->randomElement(['iPhone', 'Cuaderno', 'Detergente', 'Leche', 'Camisa', 'Monitor', 'Bolígrafo', 'Cereal']) . " " . $faker->unique()->word();
            
            $producto = Producto::create([
                'nombre' => $nombre,
                'sku' => 'SKU-' . strtoupper($faker->bothify('??###-??')),
                'descripcion' => $faker->sentence(),
                'precio' => $faker->randomFloat(2, 10, 5000)
            ]);

            // Crear stock para este producto en el local principal
            Inventario::create([
                'producto_id' => $producto->id,
                'locale_id' => $local->id,
                'stock' => $faker->numberBetween(0, 100)
            ]);
        }

        // 3. Crear una caja con 50 (Saldo Initial)
        Caja::create([
            'nombre' => 'Caja Principal-01',
            'locale_id' => $local->id,
            'saldo' => 50.00,
            'abierta' => true
        ]);
    }
}
