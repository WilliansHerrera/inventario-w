<?php

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Locale;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it logs cost change when product price is updated manually', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $producto = Producto::create([
        'nombre' => 'Producto Test',
        'sku' => 'TEST-001',
        'precio' => 100.00,
        'precio_venta' => 125.00,
        'margen' => 25.00,
    ]);

    $producto->update(['precio' => 110.00]);

    $this->assertDatabaseHas('producto_costo_historials', [
        'producto_id' => $producto->id,
        'costo_anterior' => 100.00,
        'costo_nuevo' => 110.00,
        'user_id' => $user->id,
    ]);
});

test('it updates product cost and logs history when a purchase detail is saved', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $locale = Locale::create(['nombre' => 'Sucursal Test']);
    $proveedor = Proveedor::create(['nombre' => 'Proveedor Test', 'ruc_dni' => '123']);

    $producto = Producto::create([
        'nombre' => 'Producto Test',
        'sku' => 'TEST-002',
        'precio' => 100.00,
        'precio_venta' => 125.00,
        'margen' => 25.00,
    ]);

    $compra = Compra::create([
        'proveedor_id' => $proveedor->id,
        'locale_id' => $locale->id,
        'user_id' => $user->id,
        'nro_documento' => 'FAC-001',
        'estado' => 'completado',
        'subtotal' => 0,
        'total' => 0,
    ]);

    $detalle = CompraDetalle::create([
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'costo_unitario' => 115.00,
        'subtotal' => 1150.00,
    ]);

    // Verify product cost was updated
    $producto->refresh();
    expect($producto->precio)->toBe('115.00');

    // Verify history was logged
    $this->assertDatabaseHas('producto_costo_historials', [
        'producto_id' => $producto->id,
        'compra_id' => $compra->id,
        'costo_anterior' => 100.00,
        'costo_nuevo' => 115.00,
        'user_id' => $user->id,
    ]);
});
