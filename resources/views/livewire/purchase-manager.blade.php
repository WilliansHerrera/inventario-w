<?php

use Livewire\Volt\Component;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Locale;
use App\Services\CompraService;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $step = 'list'; // 'list' or 'create'
    public $search = '';
    public $proveedor_id;
    public $nro_documento;
    public $locale_id;
    public $items = [];
    public $productSearch = '';

    public function mount()
    {
        $this->locale_id = Locale::first()->id ?? null;
    }

    public function startCreate()
    {
        $this->step = 'create';
        $this->items = [];
        $this->proveedor_id = Proveedor::first()->id ?? null;
    }

    public function addToCart($id)
    {
        $producto = Producto::findOrFail($id);
        
        if (isset($this->items[$id])) {
            $this->items[$id]['cantidad']++;
        } else {
            $this->items[$id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'costo' => (float) $producto->precio,
                'cantidad' => 1,
            ];
        }
        
        $this->productSearch = '';
    }

    public function removeItem($id)
    {
        unset($this->items[$id]);
    }

    public function save(CompraService $compraService)
    {
        if (empty($this->items)) {
            $this->dispatch('notify', ['message' => 'Agregue productos a la compra', 'type' => 'error']);
            return;
        }

        try {
            $compra = Compra::create([
                'proveedor_id' => $this->proveedor_id,
                'locale_id' => $this->locale_id,
                'user_id' => Auth::id(),
                'nro_documento' => $this->nro_documento,
                'estado' => 'borrador',
            ]);

            foreach ($this->items as $item) {
                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo'],
                    'subtotal' => $item['cantidad'] * $item['costo'],
                ]);
            }

            $compraService->processPurchase($compra);

            $this->dispatch('notify', ['message' => 'Compra procesada y stock actualizado', 'type' => 'success']);
            $this->step = 'list';
            $this->reset(['items', 'nro_documento']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function with()
    {
        $searchResults = [];
        if (strlen($this->productSearch) > 1) {
            $searchResults = Producto::where('nombre', 'like', '%' . $this->productSearch . '%')
                ->orWhere('sku', 'like', '%' . $this->productSearch . '%')
                ->limit(5)
                ->get();
        }

        return [
            'compras' => Compra::with(['proveedor', 'user'])->latest()->paginate(10),
            'proveedores' => Proveedor::all(),
            'locales' => Locale::all(),
            'searchResults' => $searchResults,
        ];
    }
}; ?>

<div class="space-y-6">
    @if($step === 'list')
    <!-- Purchases List -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Historial de Compras</h2>
            <p class="text-gray-500 dark:text-gray-400">Administración de abastecimiento y costos de entrada.</p>
        </div>
        <button wire:click="startCreate" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all flex items-center space-x-2 active:scale-95">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            <span>Nueva Compra</span>
        </button>
    </div>

    <div class="bg-white dark:bg-gray-950 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                <tr>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Documento</th>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Proveedor</th>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Fecha</th>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total</th>
                    <th class="px-8 py-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($compras as $compra)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors">
                    <td class="px-8 py-6">
                        <span class="font-bold text-gray-900 dark:text-white">#{{ $compra->nro_documento ?: $compra->id }}</span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-700 dark:text-gray-300">{{ $compra->proveedor->nombre }}</span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-widest">{{ $compra->user->name }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-gray-500 text-sm">
                        {{ $compra->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-8 py-6">
                        <span class="font-black text-gray-900 dark:text-white">{{ format_currency($compra->total) }}</span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $compra->estado === 'completada' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $compra->estado }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-12 text-center text-gray-400 opacity-50">
                        No hay compras registradas aún.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-8 py-6 border-t border-gray-100 dark:border-gray-800">
            {{ $compras->links() }}
        </div>
    </div>

    @else
    <!-- Create Purchase UI -->
    <div class="flex items-center space-x-4 mb-8">
        <button wire:click="$set('step', 'list')" class="p-4 bg-white dark:bg-gray-900 rounded-2xl text-gray-400 hover:text-indigo-500 border border-gray-100 dark:border-gray-800 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        </button>
        <div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight uppercase">Nueva Compra</h2>
            <p class="text-gray-500 dark:text-gray-400">Registro de entrada de mercancía al inventario.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Form & Items -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white dark:bg-gray-950 rounded-[2.5rem] p-8 shadow-xl border border-gray-100 dark:border-gray-800 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Proveedor</label>
                    <select wire:model="proveedor_id" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-2xl py-4 px-4 font-bold text-gray-900 dark:text-white focus:ring-4 focus:ring-indigo-500/20">
                        @foreach($proveedores as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">N° Documento/Factura</label>
                    <input wire:model="nro_documento" type="text" placeholder="FAC-001..." class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-2xl py-4 px-4 font-bold text-gray-900 dark:text-white focus:ring-4 focus:ring-indigo-500/20">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Sucursal Destino</label>
                    <select wire:model="locale_id" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-2xl py-4 px-4 font-bold text-gray-900 dark:text-white focus:ring-4 focus:ring-indigo-500/20">
                        @foreach($locales as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Product Search -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-[2rem] blur opacity-10 group-focus-within:opacity-20 transition duration-500"></div>
                <div class="relative flex items-center bg-white dark:bg-gray-950 rounded-[2rem] shadow-xl border border-gray-100 dark:border-gray-800 p-2">
                    <div class="p-4 text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input wire:model.live="productSearch" type="text" placeholder="Buscar producto por nombre o SKU para agregar..." class="flex-1 bg-transparent border-none focus:ring-0 text-lg font-medium dark:text-white">
                    
                    @if($searchResults)
                    <div class="absolute top-full left-0 right-0 mt-4 bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 z-50 overflow-hidden backdrop-blur-xl">
                        @foreach($searchResults as $product)
                        <button wire:click="addToCart({{ $product->id }})" class="w-full flex items-center p-4 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all border-b border-gray-50 dark:border-gray-800 text-left">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $product->nombre }}</h4>
                                <p class="text-[10px] text-gray-400 uppercase font-black">{{ $product->sku }}</p>
                            </div>
                            <span class="font-black text-indigo-600">Costo: {{ format_currency($product->precio) }}</span>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-gray-950 rounded-[2.5rem] shadow-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-left">Producto</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Cantidad</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Costo Unit.</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Subtotal</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($items as $id => $item)
                        <tr>
                            <td class="px-8 py-6 font-bold text-gray-900 dark:text-white">{{ $item['nombre'] }}</td>
                            <td class="px-8 py-6 text-center">
                                <input wire:model="items.{{$id}}.cantidad" type="number" class="w-20 bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-center font-black focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="px-8 py-6 text-center">
                                <input wire:model="items.{{$id}}.costo" type="number" step="0.01" class="w-28 bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-center font-black focus:ring-2 focus:ring-indigo-500">
                            </td>
                            <td class="px-8 py-6 text-right font-black text-gray-900 dark:text-white">
                                {{ format_currency($item['cantidad'] * $item['costo']) }}
                            </td>
                            <td class="px-4 py-6">
                                <button wire:click="removeItem({{$id}})" class="text-gray-300 hover:text-rose-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Summary & Action -->
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-indigo-600 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-indigo-200 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Total Compra</p>
                    @php
                        $total = array_reduce($items, function($carry, $item) {
                            return $carry + ($item['cantidad'] * $item['costo']);
                        }, 0);
                    @endphp
                    <h2 class="text-6xl font-black tracking-tighter tabular-nums">${{ number_format($total, 2) }}</h2>
                </div>
                <div class="absolute -right-12 -bottom-12 opacity-10">
                    <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

            <button wire:click="save" class="w-full py-8 bg-emerald-500 hover:bg-emerald-600 text-white rounded-[2rem] shadow-2xl shadow-emerald-500/30 text-2xl font-black uppercase tracking-tighter transition-all active:scale-95 disabled:opacity-50" {{ empty($items) ? 'disabled' : '' }}>
                Procesar e Ingresar
            </button>
        </div>
    </div>
    @endif
</div>
