<?php

use Livewire\Volt\Component;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Locale;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editingProduct = null;

    // Form fields
    public $nombre, $sku, $codigo_barra, $precio, $precio_venta, $descripcion;

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function edit($id)
    {
        $this->editingProduct = Producto::findOrFail($id);
        $this->nombre = $this->editingProduct->nombre;
        $this->sku = $this->editingProduct->sku;
        $this->codigo_barra = $this->editingProduct->codigo_barra;
        $this->precio = $this->editingProduct->precio;
        $this->precio_venta = $this->editingProduct->precio_venta;
        $this->descripcion = $this->editingProduct->descripcion;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'sku' => 'required|string|unique:productos,sku,' . ($this->editingProduct->id ?? 'NULL'),
            'precio' => 'required|numeric',
            'precio_venta' => 'required|numeric',
        ]);

        if ($this->editingProduct) {
            $this->editingProduct->update([
                'nombre' => $this->nombre,
                'sku' => $this->sku,
                'codigo_barra' => $this->codigo_barra,
                'precio' => $this->precio,
                'precio_venta' => $this->precio_venta,
                'descripcion' => $this->descripcion,
            ]);
        }

        $this->showModal = false;
        $this->reset(['nombre', 'sku', 'codigo_barra', 'precio', 'precio_venta', 'descripcion', 'editingProduct']);
    }

    public function with()
    {
        return [
            'productos' => Producto::where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('sku', 'like', '%' . $this->search . '%')
                ->orWhere('codigo_barra', 'like', '%' . $this->search . '%')
                ->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input wire:model.live="search" type="text" placeholder="Buscar por nombre, SKU o código..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm shadow-sm">
        </div>
        <button wire:click="$set('showModal', true)" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Nuevo Producto
        </button>
    </div>

    <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Precios</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stock Total</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($productos as $producto)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $producto->nombre }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $producto->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            <div>Compra: <span class="font-bold">${{ number_format($producto->precio, 2) }}</span></div>
                            <div>Venta: <span class="font-bold text-emerald-600 dark:text-emerald-400">${{ number_format($producto->precio_venta, 2) }}</span></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $stockTotal = $producto->inventarios->sum('stock'); @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $stockTotal < 10 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' }}">
                                {{ $stockTotal }} unidades
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($producto->codigo_barra)
                                <div class="bg-white p-1 rounded">
                                    {!! DNS1D::getBarcodeHTML($producto->codigo_barra, 'C128', 1, 30) !!}
                                </div>
                                <div class="text-[10px] text-center mt-1 font-mono">{{ $producto->codigo_barra }}</div>
                            @else
                                <span class="text-gray-400 italic text-xs">Sin código</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $producto->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50">
            {{ $productos->links() }}
        </div>
    </div>

    <!-- Modal for Create/Edit -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-gray-800">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">{{ $editingProduct ? 'Editar Producto' : 'Nuevo Producto' }}</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                            <input wire:model="nombre" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU</label>
                                <input wire:model="sku" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código de Barras</label>
                                <input wire:model="codigo_barra" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Compra</label>
                                <input wire:model="precio" type="number" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Venta</label>
                                <input wire:model="precio_venta" type="number" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-700 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-100">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex justify-end space-x-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-bold text-gray-700 dark:text-gray-300 hover:text-gray-500">Cancelar</button>
                    <button wire:click="save" class="px-6 py-2 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
