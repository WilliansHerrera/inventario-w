<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    /**
     * Render the barcode print view (Support single or bulk).
     */
    public function print(Request $request, $producto = null)
    {
        $productos = collect();
        $defaultQuantity = $request->get('quantity', 1);

        // Si $producto es el modelo (bound) o un ID (segmento)
        if ($producto instanceof Producto) {
            $producto->print_quantity = $defaultQuantity;
            $productos->push($producto);
        } elseif (is_numeric($producto)) {
            $model = Producto::find($producto);
            if ($model) {
                $model->print_quantity = $defaultQuantity;
                $productos->push($model);
            }
        }

        // Si se pasan IDs por query string (bulk)
        if ($productos->isEmpty() && $request->has('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $productos = Producto::whereIn('id', $ids)->get()->map(function ($p) {
                $p->print_quantity = 1;

                return $p;
            });
        }

        if ($productos->isEmpty()) {
            return back()->with('toast', ['type' => 'error', 'message' => 'No se seleccionaron productos para imprimir.']);
        }

        // Filtrar productos sin código de barras
        $validProductos = $productos->filter(fn ($p) => ! empty($p->codigo_barra));

        if ($validProductos->isEmpty()) {
            return back()->with('toast', ['type' => 'error', 'message' => 'Los productos seleccionados no tienen códigos de barras asignados.']);
        }

        return view('admin.products.barcode', [
            'productos' => $validProductos,
            'store_name' => get_global_setting('receipt_header', 'Sistema de Inventario'),
        ]);
    }
}
