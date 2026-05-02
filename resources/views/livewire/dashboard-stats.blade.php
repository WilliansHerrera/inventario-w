<?php

use Livewire\Volt\Component;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component {
    public $ventasHoy = 0;
    public $gananciaHoy = 0;
    public $capitalStock = 0;
    public $alertasResurtido = 0;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $hoy = Carbon::today();

        // Ventas del día
        $this->ventasHoy = Venta::whereDate('created_at', $hoy)->sum('total') ?? 0;

        // Ganancia neta (SQL aggregation to avoid memory exhaustion)
        $this->gananciaHoy = VentaDetalle::join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->whereDate('ventas.created_at', $hoy)
            ->sum(DB::raw('(venta_detalles.precio_unitario - productos.precio) * venta_detalles.cantidad')) ?? 0;

        // Capital en stock
        $this->capitalStock = Inventario::join('productos', 'inventarios.producto_id', '=', 'productos.id')
            ->sum(DB::raw('inventarios.stock * productos.precio')) ?? 0;

        // Alertas de resurtido
        $this->alertasResurtido = DB::table('inventarios')
            ->select('producto_id')
            ->groupBy('producto_id')
            ->havingRaw('SUM(stock) < 10')
            ->get()
            ->count();
    }
}; ?>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6" wire:init="loadStats">
    <!-- Ventas del Día -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] group hover:-translate-y-1 transition-all duration-300">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent dark:from-indigo-500/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold uppercase tracking-wider">Ingresos (Hoy)</p>
                <div class="flex items-end mt-2">
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white leading-none">
                        <span wire:loading.remove>${{ number_format($ventasHoy, 2) }}</span>
                        <span wire:loading class="h-8 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded inline-block"></span>
                    </h3>
                </div>
            </div>
            <div class="bg-indigo-50 dark:bg-indigo-900/50 p-4 rounded-xl text-indigo-600 dark:text-indigo-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Ganancia Neta -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] group hover:-translate-y-1 transition-all duration-300">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-transparent dark:from-emerald-500/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold uppercase tracking-wider">Margen Neto</p>
                <div class="flex items-end mt-2">
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white leading-none">
                        <span wire:loading.remove>${{ number_format($gananciaHoy, 2) }}</span>
                        <span wire:loading class="h-8 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded inline-block"></span>
                    </h3>
                </div>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/50 p-4 rounded-xl text-emerald-600 dark:text-emerald-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Capital en Stock -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] group hover:-translate-y-1 transition-all duration-300">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-transparent dark:from-amber-500/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold uppercase tracking-wider">Valor Inventario</p>
                <div class="flex items-end mt-2">
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white leading-none">
                        <span wire:loading.remove>${{ number_format($capitalStock, 2) }}</span>
                        <span wire:loading class="h-8 w-24 bg-gray-200 dark:bg-gray-700 animate-pulse rounded inline-block"></span>
                    </h3>
                </div>
            </div>
            <div class="bg-amber-50 dark:bg-amber-900/50 p-4 rounded-xl text-amber-600 dark:text-amber-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Alertas de Resurtido -->
    <div class="relative overflow-hidden bg-white dark:bg-gray-900/80 backdrop-blur-md rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] group hover:-translate-y-1 transition-all duration-300">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-transparent dark:from-rose-500/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <div class="relative flex items-center justify-between">
            <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-semibold uppercase tracking-wider">Bajo Stock</p>
                <div class="flex items-end mt-2">
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white leading-none">
                        <span wire:loading.remove>{{ $alertasResurtido }}</span>
                        <span wire:loading class="h-8 w-12 bg-gray-200 dark:bg-gray-700 animate-pulse rounded inline-block"></span>
                    </h3>
                </div>
            </div>
            <div class="bg-rose-50 dark:bg-rose-900/50 p-4 rounded-xl text-rose-600 dark:text-rose-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>
</div>
