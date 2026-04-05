<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use App\Models\Locale;
use App\Models\Inventario;

class SeedRealData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Camisa de Lino Azul', 'sku' => 'CAM-BL-01', 'codigo_barra' => '75010001', 'precio' => 450.00],
            ['nombre' => 'Pantalón Denim Clásico', 'sku' => 'PAN-DN-02', 'codigo_barra' => '75010002', 'precio' => 899.00],
            ['nombre' => 'Zapatos de Cuero Café', 'sku' => 'ZAP-CU-03', 'codigo_barra' => '75010003', 'precio' => 1250.00],
            ['nombre' => 'Gorra Sport Blanca', 'sku' => 'GOR-SP-04', 'codigo_barra' => '75010004', 'precio' => 299.00],
            ['nombre' => 'Chaqueta Impermeable Negra', 'sku' => 'CHA-IM-05', 'codigo_barra' => '75010005', 'precio' => 1500.00],
            ['nombre' => 'Sudadera Algodón Gris', 'sku' => 'SUD-AL-06', 'codigo_barra' => '75010006', 'precio' => 650.00],
            ['nombre' => 'Cinturón de Cuero Negro', 'sku' => 'CIN-CU-07', 'codigo_barra' => '75010007', 'precio' => 350.00],
            ['nombre' => 'Calcetines Pack x3', 'sku' => 'CAL-PA-08', 'codigo_barra' => '75010008', 'precio' => 150.00],
            ['nombre' => 'Reloj Urban Minimalist', 'sku' => 'REL-UR-09', 'codigo_barra' => '75010009', 'precio' => 2100.00],
            ['nombre' => 'Mochila Exploradora Pro', 'sku' => 'MOC-EX-10', 'codigo_barra' => '75010010', 'precio' => 1850.00],
        ];

        $locales = Locale::all();

        foreach ($productos as $p) {
            $prod = Producto::updateOrCreate(['sku' => $p['sku']], $p);
            
            foreach ($locales as $l) {
                Inventario::updateOrCreate(
                    ['producto_id' => $prod->id, 'locale_id' => $l->id],
                    ['stock' => rand(10, 50)]
                );
            }
        }
    }
}
