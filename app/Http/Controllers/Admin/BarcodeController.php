<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    /**
     * Render the barcode print view.
     */
    public function print(Producto $producto)
    {
        if (!$producto->codigo_barra) {
            return back()->with('toast', 'El producto no tiene un código de barras asignado.')->danger();
        }

        return view('admin.products.barcode', compact('producto'));
    }
}
