<?php

use Livewire\Volt\Component;
use App\Models\Caja;
use App\Services\CashRegisterService;
use Illuminate\Support\Facades\DB;

new class extends Component {
    public $showModal = false;
    public $modalMode = 'open'; // 'open' or 'close'
    public $cajaId = null;
    public $monto = 0;
    public $descripcion = '';

    public function startOpening($id)
    {
        $this->cajaId = $id;
        $this->monto = get_global_setting('default_opening_amount', 50.0);
        $this->modalMode = 'open';
        $this->showModal = true;
    }

    public function startClosing($id)
    {
        $this->cajaId = $id;
        $caja = Caja::findOrFail($id);
        $this->monto = (float) $caja->saldo;
        $this->modalMode = 'close';
        $this->showModal = true;
    }

    public function process(CashRegisterService $cashService)
    {
        $caja = Caja::findOrFail($this->cajaId);

        try {
            if ($this->modalMode === 'open') {
                $cashService->openShift($caja, (float) $this->monto);
                $this->dispatch('notify', ['message' => 'Caja abierta con éxito', 'type' => 'success']);
            } else {
                $cashService->closeShift($caja, (float) $this->monto);
                $this->dispatch('notify', ['message' => 'Caja cerrada con éxito', 'type' => 'success']);
            }
            
            $this->showModal = false;
            $this->reset(['cajaId', 'monto', 'descripcion']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function with()
    {
        return [
            'cajas' => Caja::with(['sucursal', 'turnoActivo.user'])->get(),
        ];
    }
}; ?>

<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Gestión de Cajas</h2>
            <p class="text-gray-500 dark:text-gray-400">Control de turnos, arqueos y saldos en tiempo real.</p>
        </div>
    </div>

    <!-- Cajas Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($cajas as $caja)
        <div class="relative group">
            <div class="absolute -inset-0.5 bg-gradient-to-r {{ $caja->abierta ? 'from-emerald-500 to-teal-500' : 'from-gray-200 to-gray-300 dark:from-gray-800 dark:to-gray-700' }} rounded-[2rem] blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
            
            <div class="relative bg-white dark:bg-gray-950 rounded-[2rem] p-8 shadow-xl border border-gray-100 dark:border-gray-800 flex flex-col h-full">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-3 {{ $caja->abierta ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }} rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $caja->nombre }}</h3>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $caja->sucursal->nombre }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $caja->abierta ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                            {{ $caja->abierta ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                </div>

                <div class="flex-1 space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Efectivo en Caja</p>
                        <p class="text-4xl font-black text-gray-900 dark:text-white tabular-nums tracking-tighter">
                            {{ format_currency($caja->saldo) }}
                        </p>
                    </div>

                    @if($caja->abierta && $caja->turnoActivo)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            <p class="text-xs font-bold text-gray-600 dark:text-gray-400">Turno de: {{ $caja->turnoActivo->user->name }}</p>
                        </div>
                        <p class="text-[10px] text-gray-400">Desde: {{ $caja->turnoActivo->abierto_at->format('d/m H:i') }}</p>
                    </div>
                    @endif
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                    @if($caja->abierta)
                    <button wire:click="startClosing({{ $caja->id }})" class="w-full py-4 bg-white dark:bg-gray-900 border-2 border-rose-100 dark:border-rose-900/30 text-rose-600 dark:text-rose-400 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z" /></svg>
                        <span>Cerrar Turno (Arqueo)</span>
                    </button>
                    @else
                    <button wire:click="startOpening({{ $caja->id }})" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all flex items-center justify-center space-x-2 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0 13a9 9 0 110-18 9 9 0 010 18z" /></svg>
                        <span>Abrir Nueva Jornada</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-sm transition-all" x-data="{ show: true }">
        <div class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden" x-show="show" x-transition>
            <div class="p-10">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="p-4 {{ $modalMode === 'open' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-600' }} rounded-3xl">
                        @if($modalMode === 'open')
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        @else
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight uppercase">
                            {{ $modalMode === 'open' ? 'Apertura de Caja' : 'Cierre de Caja' }}
                        </h3>
                        <p class="text-gray-500 text-sm">Registro de auditoría y fondo de efectivo.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">
                            {{ $modalMode === 'open' ? 'Fondo Inicial en Efectivo' : 'Efectivo Real en Caja (Arqueo)' }}
                        </label>
                        <div class="relative">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-2xl font-black text-gray-400">$</span>
                            <input wire:model="monto" type="number" step="0.01" class="w-full bg-gray-50 dark:bg-gray-800/50 border-none rounded-3xl py-6 pl-12 pr-8 text-3xl font-black text-gray-900 dark:text-white focus:ring-4 focus:ring-indigo-500/20 transition-all" autofocus>
                        </div>
                    </div>

                    @if($modalMode === 'close')
                    <div class="p-6 bg-amber-50 dark:bg-amber-900/10 rounded-3xl border border-amber-100 dark:border-amber-900/30">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                                Ingrese el monto total que ha contado físicamente. El sistema calculará automáticamente cualquier diferencia contra el saldo registrado.
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/30 px-10 py-8 flex justify-end items-center space-x-6">
                <button wire:click="$set('showModal', false)" class="text-sm font-black text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 uppercase tracking-widest transition-colors">
                    Cancelar
                </button>
                <button wire:click="process" class="px-10 py-4 {{ $modalMode === 'open' ? 'bg-indigo-600 shadow-indigo-500/20' : 'bg-rose-600 shadow-rose-500/20' }} text-white font-black rounded-2xl shadow-xl hover:scale-105 active:scale-95 transition-all text-sm uppercase tracking-widest">
                    {{ $modalMode === 'open' ? 'Confirmar Apertura' : 'Ejecutar Cierre' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
