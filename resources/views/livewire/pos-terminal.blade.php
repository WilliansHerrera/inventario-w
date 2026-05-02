<?php

use Livewire\Volt\Component;
use App\Models\Producto;
use App\Models\Caja;
use App\Services\POSService;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $search = '';
    public $cart = [];
    public $total = 0;
    public $cajaId;
    public $metodoPago = 'efectivo';

    public function mount()
    {
        // Select the first open caja by default
        $caja = Caja::where('abierta', true)->first();
        $this->cajaId = $caja->id ?? null;
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 3) {
            // Check for exact barcode/sku match for scanners
            $product = Producto::where('codigo_barra', $this->search)
                ->orWhere('sku', $this->search)
                ->first();
            
            if ($product) {
                $this->addToCart($product->id);
                $this->search = '';
            }
        }
    }

    public function addToCart($id)
    {
        $product = Producto::findOrFail($id);
        
        if (isset($this->cart[$id])) {
            $this->cart[$id]['cantidad']++;
        } else {
            $this->cart[$id] = [
                'id' => $product->id,
                'nombre' => $product->nombre,
                'precio' => (float) $product->precio_venta,
                'cantidad' => 1,
            ];
        }
        
        $this->calculateTotal();
    }

    public function updateQuantity($id, $qty)
    {
        if ($qty <= 0) {
            $this->removeFromCart($id);
            return;
        }
        
        if (isset($this->cart[$id])) {
            $this->cart[$id]['cantidad'] = $qty;
            $this->calculateTotal();
        }
    }

    public function removeFromCart($id)
    {
        unset($this->cart[$id]);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = array_reduce($this->cart, function($carry, $item) {
            return $carry + ($item['precio'] * $item['cantidad']);
        }, 0);
    }

    public function processSale(POSService $posService)
    {
        if (!$this->cajaId) {
            $this->dispatch('notify', ['message' => __('Seleccione una caja abierta'), 'type' => 'error']);
            return;
        }

        if (empty($this->cart)) {
            $this->dispatch('notify', ['message' => __('El carrito está vacío'), 'type' => 'error']);
            return;
        }

        try {
            $saleData = [
                'caja_id' => $this->cajaId,
                'total' => $this->total,
                'metodo_pago' => $this->metodoPago,
                'items' => array_map(function($item) {
                    return [
                        'producto_id' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                    ];
                }, $this->cart),
            ];

            $posService->processSale($saleData);

            $this->dispatch('notify', ['message' => __('¡Venta Realizada con Éxito!'), 'type' => 'success']);
            $this->reset(['cart', 'total', 'search']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function with()
    {
        $searchResults = [];
        if (strlen($this->search) > 1) {
            $searchResults = Producto::where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%')
                ->orWhere('codigo_barra', 'like', '%' . $this->search . '%')
                ->limit(8)
                ->get();
        }

        return [
            'searchResults' => $searchResults,
            'cajas' => Caja::where('abierta', true)->get(),
        ];
    }
}; ?>

<div class="flex flex-col lg:flex-row gap-8 h-[calc(100vh-140px)] overflow-hidden">
    <!-- Main POS Area -->
    <div class="flex-1 flex flex-col min-w-0 space-y-6">
        <!-- Search & Scanner Bar -->
        <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-[2rem] blur opacity-20 group-focus-within:opacity-40 transition duration-500"></div>
            <div class="relative flex items-center bg-white dark:bg-gray-950 rounded-[2rem] shadow-xl border border-indigo-100 dark:border-gray-800 overflow-hidden p-2">
                <div class="p-4 text-indigo-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input wire:model.live="search" type="text" placeholder="Escanee código de barras o busque por nombre..." class="flex-1 bg-transparent border-none focus:ring-0 text-xl font-medium placeholder-gray-400 dark:text-white" autofocus>
                
                @if($search)
                <button wire:click="$set('search', '')" class="p-4 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                @endif

                <!-- Results Dropdown -->
                @if(count($searchResults) > 0)
                <div class="absolute top-full left-0 right-0 mt-4 bg-white dark:bg-gray-900 rounded-[2rem] shadow-2xl border border-gray-100 dark:border-gray-800 z-50 overflow-hidden max-h-[400px] overflow-y-auto backdrop-blur-xl">
                    @foreach($searchResults as $product)
                    <button wire:click="addToCart({{ $product->id }})" class="w-full flex items-center p-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all border-b border-gray-50 dark:border-gray-800 group">
                        <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                        <div class="ml-4 text-left flex-1">
                            <h4 class="font-bold text-gray-900 dark:text-white">{{ $product->nombre }}</h4>
                            <p class="text-xs text-gray-500 tracking-widest font-black uppercase">{{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-black text-indigo-600 dark:text-indigo-400">{{ format_currency($product->precio_venta) }}</p>
                            <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest">Stock: {{ $product->inventario()->sum('stock') }}</p>
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Cart Table -->
        <div class="flex-1 bg-white dark:bg-gray-950 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 flex flex-col overflow-hidden">
            <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 flex justify-between items-center">
                <h3 class="font-black text-xs uppercase tracking-widest text-gray-400">Detalle de Venta</h3>
                <span class="bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 text-[10px] font-black px-3 py-1 rounded-full">{{ count($cart) }} PRODUCTOS</span>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                @forelse($cart as $item)
                <div class="flex items-center bg-gray-50 dark:bg-gray-900/40 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-800 group hover:shadow-lg transition-all">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 dark:text-white truncate">{{ $item['nombre'] }}</h4>
                        <p class="text-xs text-gray-500">{{ format_currency($item['precio']) }} c/u</p>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center bg-white dark:bg-gray-950 rounded-2xl border border-gray-100 dark:border-gray-800 p-1 shadow-sm">
                            <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['cantidad'] - 1 }})" class="p-2 text-gray-400 hover:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                            </button>
                            <input type="number" value="{{ $item['cantidad'] }}" wire:change="updateQuantity({{ $item['id'] }}, $event.target.value)" class="w-12 bg-transparent border-none text-center font-black text-gray-900 dark:text-white focus:ring-0 p-0">
                            <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['cantidad'] + 1 }})" class="p-2 text-gray-400 hover:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </button>
                        </div>
                        
                        <div class="w-32 text-right">
                            <p class="text-xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">
                                {{ format_currency($item['precio'] * $item['cantidad']) }}
                            </p>
                        </div>

                        <button wire:click="removeFromCart({{ $item['id'] }})" class="p-3 text-gray-300 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
                @empty
                <div class="h-full flex flex-col items-center justify-center space-y-4 opacity-20">
                    <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                    <p class="text-2xl font-black uppercase tracking-tighter">Terminal Lista</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar: Payment & Summary -->
    <div class="w-full lg:w-[400px] flex flex-col space-y-6">
        <!-- Totals Card -->
        <div class="bg-indigo-600 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-indigo-200 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Monto Total</p>
                <div class="flex items-baseline space-x-2">
                    <span class="text-2xl font-bold opacity-50">$</span>
                    <h2 class="text-7xl font-black tracking-tighter tabular-nums">{{ number_format($total, 2) }}</h2>
                </div>
            </div>
            <!-- Decorative Icon -->
            <div class="absolute -right-12 -bottom-12 opacity-10">
                <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <!-- Checkout Actions -->
        <div class="bg-white dark:bg-gray-950 rounded-[2.5rem] p-10 shadow-xl border border-gray-100 dark:border-gray-800 space-y-8 flex-1 flex flex-col">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Seleccionar Caja</label>
                <div class="grid grid-cols-1 gap-3">
                    @foreach($cajas as $caja)
                    <button wire:click="$set('cajaId', {{ $caja->id }})" class="flex items-center justify-between p-4 rounded-2xl border-2 transition-all {{ $cajaId == $caja->id ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400' : 'border-gray-50 dark:border-gray-800 text-gray-400' }}">
                        <span class="font-bold">{{ $caja->nombre }}</span>
                        <span class="text-[10px] font-black">{{ format_currency($caja->saldo) }}</span>
                    </button>
                    @endforeach
                    @if(count($cajas) == 0)
                    <div class="p-4 bg-rose-50 dark:bg-rose-900/20 text-rose-600 rounded-2xl text-xs font-bold text-center">
                        No hay cajas abiertas.
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Método de Pago</label>
                <div class="grid grid-cols-2 gap-4">
                    <button wire:click="$set('metodoPago', 'efectivo')" class="group p-6 rounded-3xl border-2 transition-all text-center {{ $metodoPago === 'efectivo' ? 'bg-indigo-600 border-indigo-600 text-white shadow-xl shadow-indigo-500/30' : 'bg-gray-50 dark:bg-gray-900 border-gray-100 dark:border-gray-800 text-gray-400' }}">
                        <svg class="w-8 h-8 mx-auto mb-2 {{ $metodoPago === 'efectivo' ? 'text-white' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <span class="text-[10px] font-black uppercase tracking-widest">Efectivo</span>
                    </button>
                    <button wire:click="$set('metodoPago', 'tarjeta')" class="group p-6 rounded-3xl border-2 transition-all text-center {{ $metodoPago === 'tarjeta' ? 'bg-indigo-600 border-indigo-600 text-white shadow-xl shadow-indigo-500/30' : 'bg-gray-50 dark:bg-gray-900 border-gray-100 dark:border-gray-800 text-gray-400' }}">
                        <svg class="w-8 h-8 mx-auto mb-2 {{ $metodoPago === 'tarjeta' ? 'text-white' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        <span class="text-[10px] font-black uppercase tracking-widest">Tarjeta</span>
                    </button>
                </div>
            </div>

            <div class="flex-1 flex items-end">
                <button wire:click="processSale" class="w-full py-8 bg-emerald-500 hover:bg-emerald-600 text-white rounded-[2rem] shadow-2xl shadow-emerald-500/30 text-2xl font-black uppercase tracking-tighter transition-all active:scale-95 disabled:opacity-50" {{ empty($cart) || !$cajaId ? 'disabled' : '' }}>
                    Finalizar Venta
                </button>
            </div>
        </div>
    </div>
</div>
