<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ScrapedProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => '75999', 'nombre' => 'Frijol Rojo Seda Don Frijol 454 g', 'precio_venta' => 1.50],
            ['sku' => '3459', 'nombre' => 'Frijol Rojo Don Frijol 1 816 g', 'precio_venta' => 5.50],
            ['sku' => '5513', 'nombre' => 'Frijol Rojo Seda Don Frijol 1 816 g', 'precio_venta' => 4.99],
            ['sku' => '11201', 'nombre' => 'Frijol Procesado Don Frijol 227 g', 'precio_venta' => 0.65],
            ['sku' => '103929', 'nombre' => 'Frijol Don Frijol Doy Pack 850 g', 'precio_venta' => 1.14],
            ['sku' => '11246', 'nombre' => 'Frijol Procesado Don Frijol 2 270 g', 'precio_venta' => 3.10],
            ['sku' => '3362', 'nombre' => 'Frijol Blanco Don Frijol 454 g', 'precio_venta' => 2.25],
            ['sku' => '3356', 'nombre' => 'Frijol Rojo Don Frijol 908 g', 'precio_venta' => 2.75],
            ['sku' => '3370', 'nombre' => 'Frijol Rojo Seda Don Frijol 908 g', 'precio_venta' => 2.99],
            ['sku' => '106770', 'nombre' => 'Frijol Procesado Con Chile Don Frijol 227 g', 'precio_venta' => 0.75],
            ['sku' => '6142', 'nombre' => 'Frijol Negro Don Frijol 1 816 g', 'precio_venta' => 5.00],
            ['sku' => '11277', 'nombre' => 'Frijoles Ducal Rojos Volteados 227 g', 'precio_venta' => 0.80],
            ['sku' => '80765', 'nombre' => 'Frijoles Dany Rojos Volteados 993g unidad', 'precio_venta' => 1.50],
            ['sku' => '111858', 'nombre' => 'Frijoles Rojos Volteados Santa Gracia 800 g', 'precio_venta' => 0.90],
            ['sku' => '5719', 'nombre' => 'Frijoles Ducal Rojos Volteados 400 g', 'precio_venta' => 1.16],
            ['sku' => '11219', 'nombre' => 'Frijoles Enteros Bayos La Sierra 560 g Lata', 'precio_venta' => 2.10],
            ['sku' => '11291', 'nombre' => 'Frijoles Rojos Omoa 908 g', 'precio_venta' => 2.85],
            ['sku' => '5717', 'nombre' => 'Frijol Rojo Volteado La Chula 400 g', 'precio_venta' => 0.90],
            ['sku' => '87730', 'nombre' => 'Frijol Rojo Seda Selectos 908 g', 'precio_venta' => 2.25],
            ['sku' => '21015', 'nombre' => 'Frijoles Doña Lita Blanco 454 g Bolsa', 'precio_venta' => 1.65],
        ];

        foreach ($products as $p) {
            $cost = round($p['precio_venta'] * 0.8, 2); // 80% cost
            $margen = $p['precio_venta'] - $cost;

            Producto::updateOrCreate(
                ['sku' => $p['sku']],
                [
                    'nombre' => $p['nombre'],
                    'precio' => $cost,
                    'precio_venta' => $p['precio_venta'],
                    'margen' => $margen,
                    'codigo_barra' => $p['sku'], // using SKU as barcode for these
                ]
            );
        }
    }
}
