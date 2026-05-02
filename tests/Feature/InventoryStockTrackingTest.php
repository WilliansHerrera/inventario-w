<?php

use App\Models\Producto;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\User;
use App\Models\Locale;
use App\Models\Proveedor;
use App\Models\Caja;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it increases stock and logs movement when a purchase is made', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $locale = Locale::create(['nombre' => 'Sucursal Test']);
    $proveedor = Proveedor::create(['nombre' => 'Proveedor Test', 'ruc_dni' => '123']);
    $producto = Producto::create([
        'nombre' => 'Producto Test',
        'sku' => 'TEST-001',
        'precio' => 100.00,
        'precio_venta' => 125.00,
        'margen' => 25.00
    ]);

    $compra = Compra::create([
        'proveedor_id' => $proveedor->id,
        'locale_id' => $locale->id,
        'user_id' => $user->id,
        'nro_documento' => 'PUR-123',
        'estado' => 'completado',
        'subtotal' => 0,
        'total' => 0
    ]);

    CompraDetalle::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 50,
        'costo_unitario' => 100.00,
        'subtotal' => 5000.00
    ]);

    $inventario = Inventario::where('producto_id', $producto->id)->where('locale_id', $locale->id)->first();
    expect($inventario->stock)->toEqual(50);

    $this->assertDatabaseHas('inventario_movimientos', [
        'inventario_id' => $inventario->id,
        'cantidad' => 50,
        'tipo' => 'compra',
        'user_id' => $user->id
    ]);
});

test('it decreases stock and logs movement when a sale is made', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $locale = Locale::create(['nombre' => 'Sucursal Test']);
    $caja = Caja::create(['nombre' => 'Caja 1', 'locale_id' => $locale->id]);
    $producto = Producto::create([
        'nombre' => 'Producto Test',
        'sku' => 'TEST-002',
        'precio' => 100.00,
        'precio_venta' => 125.00,
        'margen' => 25.00
    ]);

    // Initial stock
    $inventario = Inventario::create([
        'producto_id' => $producto->id,
        'locale_id' => $locale->id,
        'stock' => 100
    ]);

    $venta = Venta::create([
        'caja_id' => $caja->id,
        'user_id' => $user->id,
        'total' => 125.00,
        'metodo_pago' => 'efectivo'
    ]);

    VentaDetalle::create([
        'venta_id' => $venta->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'precio_unitario' => 125.00,
        'subtotal' => 1250.00
    ]);

    $inventario->refresh();
    expect($inventario->stock)->toEqual(90);

    $this->assertDatabaseHas('inventario_movimientos', [
        'inventario_id' => $inventario->id,
        'cantidad' => -10,
        'tipo' => 'venta',
        'user_id' => $user->id
    ]);
});
