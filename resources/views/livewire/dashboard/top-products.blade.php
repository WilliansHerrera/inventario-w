<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $topProducts = [];

    public function mount()
    {
        $this->loadTopProducts();
    }

    public function loadTopProducts()
    {
        $this->topProducts = DB::table('venta_detalles')
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->select(
                'productos.nombre',
                'productos.sku',
                DB::raw('SUM(venta_detalles.cantidad) as total_vendido'),
                DB::raw('SUM(venta_detalles.cantidad * venta_detalles.precio_unitario) as total_ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre', 'productos.sku')
            ->orderByDesc('total_ingresos')
            ->limit(5)
            ->get();
    }
}; ?>

<div class="bg-white dark:bg-gray-900 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-2xl border border-gray-100 dark:border-gray-800" wire:init="loadTopProducts">
    <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            Productos Más Vendidos (Histórico)
        </h3>
    </div>
    
    <div class="p-0">
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50/50 dark:bg-gray-800/50 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Producto</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Volumen</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Ingresos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($topProducts as $product)
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $product->nombre }}</div>
                                <div class="text-xs text-gray-400">{{ $product->sku ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                                    {{ number_format($product->total_vendido) }} unds
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">
                                ${{ number_format($product->total_ingresos, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div wire:loading.remove>No hay datos de ventas disponibles.</div>
                                <div wire:loading class="flex justify-center items-center space-x-2">
                                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Cargando datos...</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
