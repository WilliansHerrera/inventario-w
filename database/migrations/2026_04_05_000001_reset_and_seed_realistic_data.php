<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-shot data reset and realistic seed for Inventario-w.
 * Clears all business data, then creates 2 locales, 3 cajas, 
 * and ~20 realistic products for a small retail store.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────
        // 1. CLEAR ALL BUSINESS DATA (preserve users)
        // ──────────────────────────────────────────────
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('inventario_movimientos')->truncate();
        DB::table('caja_movimientos')->truncate();
        DB::table('venta_detalles')->truncate();
        DB::table('ventas')->truncate();
        DB::table('inventarios')->truncate();
        DB::table('cajas')->truncate();
        DB::table('productos')->truncate();
        DB::table('locales')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ──────────────────────────────────────────────
        // 2. LOCALES (Sucursales)
        // ──────────────────────────────────────────────
        $now = now();
        DB::table('locales')->insert([
            ['id' => 1, 'nombre' => 'Tienda Central',   'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nombre' => 'Sucursal Norte',   'created_at' => $now, 'updated_at' => $now],
        ]);

        // ──────────────────────────────────────────────
        // 3. CAJAS (2 en Tienda Central, 1 en Sucursal Norte)
        // ──────────────────────────────────────────────
        DB::table('cajas')->insert([
            ['id' => 1, 'nombre' => 'Caja 01 — Central',   'locale_id' => 1, 'saldo' => 500.00, 'abierta' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nombre' => 'Caja 02 — Central',   'locale_id' => 1, 'saldo' => 300.00, 'abierta' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nombre' => 'Caja 01 — Norte',     'locale_id' => 2, 'saldo' => 200.00, 'abierta' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);



        // ──────────────────────────────────────────────
        // 4. PRODUCTOS (20 artículos realistas)
        // ──────────────────────────────────────────────
        $productos = [
            // Bebidas
            ['sku' => 'BEB-001', 'nombre' => 'Agua Natural 500ml',          'precio' => 5.00,  'precio_venta' => 8.00],
            ['sku' => 'BEB-002', 'nombre' => 'Coca-Cola 600ml',             'precio' => 9.00,  'precio_venta' => 14.00],
            ['sku' => 'BEB-003', 'nombre' => 'Jugo de Naranja 350ml',       'precio' => 8.00,  'precio_venta' => 12.00],
            ['sku' => 'BEB-004', 'nombre' => 'Café Molido 250g',            'precio' => 35.00, 'precio_venta' => 55.00],
            // Snacks
            ['sku' => 'SNK-001', 'nombre' => 'Papas Fritas 42g',            'precio' => 10.00, 'precio_venta' => 16.00],
            ['sku' => 'SNK-002', 'nombre' => 'Galletas María 200g',         'precio' => 12.00, 'precio_venta' => 18.00],
            ['sku' => 'SNK-003', 'nombre' => 'Chicles Orbit Menta x12',     'precio' => 8.00,  'precio_venta' => 12.00],
            // Higiene personal
            ['sku' => 'HIG-001', 'nombre' => 'Jabón de Baño 150g',          'precio' => 9.00,  'precio_venta' => 15.00],
            ['sku' => 'HIG-002', 'nombre' => 'Shampoo 200ml',               'precio' => 28.00, 'precio_venta' => 45.00],
            ['sku' => 'HIG-003', 'nombre' => 'Papel Higiénico x4 rollos',   'precio' => 25.00, 'precio_venta' => 38.00],
            ['sku' => 'HIG-004', 'nombre' => 'Pasta Dental 100ml',          'precio' => 15.00, 'precio_venta' => 24.00],
            // Limpieza
            ['sku' => 'LIM-001', 'nombre' => 'Detergente en Polvo 500g',    'precio' => 20.00, 'precio_venta' => 32.00],
            ['sku' => 'LIM-002', 'nombre' => 'Cloro 1 Litro',               'precio' => 12.00, 'precio_venta' => 18.00],
            ['sku' => 'LIM-003', 'nombre' => 'Esponja de Limpiar x2',       'precio' => 7.00,  'precio_venta' => 12.00],
            // Abarrotes
            ['sku' => 'ABA-001', 'nombre' => 'Arroz Blanco 1 kg',           'precio' => 18.00, 'precio_venta' => 25.00],
            ['sku' => 'ABA-002', 'nombre' => 'Frijol Negro 1 kg',           'precio' => 22.00, 'precio_venta' => 32.00],
            ['sku' => 'ABA-003', 'nombre' => 'Aceite Vegetal 1 Litro',      'precio' => 28.00, 'precio_venta' => 42.00],
            ['sku' => 'ABA-004', 'nombre' => 'Azúcar Blanca 1 kg',          'precio' => 16.00, 'precio_venta' => 24.00],
            // Lácteos
            ['sku' => 'LAC-001', 'nombre' => 'Leche Entera 1 Litro',        'precio' => 15.00, 'precio_venta' => 22.00],
            ['sku' => 'LAC-002', 'nombre' => 'Queso Fresco 250g',           'precio' => 30.00, 'precio_venta' => 48.00],
        ];

        $productoIds = [];
        foreach ($productos as $p) {
            $id = DB::table('productos')->insertGetId([
                'sku'         => $p['sku'],
                'nombre'      => $p['nombre'],
                'precio'      => $p['precio'],
                'precio_venta'=> $p['precio_venta'],
                'codigo_barra'=> null,
                'descripcion' => null,
                'imagen'      => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $productoIds[] = $id;
        }

        // ──────────────────────────────────────────────
        // 5. INVENTARIO (stock por sucursal)
        //    Tienda Central: stock alto
        //    Sucursal Norte: stock moderado
        // ──────────────────────────────────────────────
        $stockCentral = [50, 48, 60, 24, 80, 72, 100, 60, 36, 48, 55, 30, 40, 60, 90, 75, 55, 80, 65, 40];
        $stockNorte   = [30, 24, 36, 12, 40, 36, 60, 30, 18, 24, 28, 15, 20, 30, 50, 40, 30, 45, 35, 20];

        foreach ($productoIds as $i => $productoId) {
            // Tienda Central
            DB::table('inventarios')->insert([
                'producto_id' => $productoId,
                'locale_id'   => 1,
                'stock'       => $stockCentral[$i] ?? 30,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            // Sucursal Norte
            DB::table('inventarios')->insert([
                'producto_id' => $productoId,
                'locale_id'   => 2,
                'stock'       => $stockNorte[$i] ?? 15,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible intentionally — this is a reset migration.
    }
};
