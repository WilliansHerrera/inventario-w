<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    black: ['Inter', 'sans-serif'],
                },
            }
        }
    }
</script>

<div
    x-data="pos()"
    x-init="init()"
    @keydown.window="handleKey($event)"
    class="flex flex-col w-full bg-slate-50 text-slate-800 min-h-screen lg:min-h-0 lg:h-[calc(100vh-4rem)] overflow-y-auto lg:overflow-hidden font-[Inter,sans-serif]"
>
    {{-- Audio para feedback Premium --}}
    <audio x-ref="scanSound" src="data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YTtvT19vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb09vT29Pb08="></audio>
    {{-- ══════════════════════════════════════ --}}
    {{-- HEADER (Premium styled)                --}}
    {{-- ══════════════════════════════════════ --}}
    <div class="flex items-center gap-4 px-6 h-16 bg-slate-900 text-white shrink-0 shadow-xl z-20">
        
        {{-- Brand --}}
        <div class="flex items-center gap-3 select-none">
            <div class="p-2 bg-indigo-500 rounded-xl shadow-lg shadow-indigo-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" 
                          d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="hidden sm:block">
                <p class="font-black text-sm tracking-tighter uppercase italic">Inventario-W</p>
                <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-widest leading-none">Terminal POS v2.0</p>
            </div>
        </div>

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Caja selector (Refined) --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button
                @click="open = !open"
                class="flex items-center gap-3 text-xs font-bold px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 hover:border-indigo-400 transition-all active:scale-95 group"
            >
                {{-- Status Pulse --}}
                <div class="relative flex h-2.5 w-2.5">
                    <span :class="cajaId ? 'animate-ping bg-emerald-400 opacity-75' : 'bg-slate-500'" 
                          class="absolute inline-flex h-full w-full rounded-full"></span>
                    <span :class="cajaId ? 'bg-emerald-500' : 'bg-slate-500'" 
                          class="relative inline-flex rounded-full h-2.5 w-2.5"></span>
                </div>

                <div class="text-left">
                    <p class="text-[9px] text-slate-400 uppercase leading-none mb-0.5" x-text="cajaId ? 'Tpv Activa' : 'Sin caja'"></p>
                    <span class="truncate max-w-[120px]" x-text="cajaNombre || 'SELECCIONAR CAJA'"></span>
                </div>

                <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 top-full mt-2 w-72 bg-white text-slate-800 border border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden backdrop-blur-md">
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Cajas Disponibles</span>
                </div>
                
                @php $cajasDisponibles = \App\Models\Caja::with('sucursal')->get(); @endphp
                <div class="max-h-80 overflow-y-auto py-2">
                    @forelse($cajasDisponibles as $caja)
                        <button
                            @click="selectCaja({{ $caja->id }}, '{{ addslashes($caja->nombre) }}', {{ $caja->locale_id }}, {{ $caja->abierta ? 'true' : 'false' }}, {{ $caja->apertura_automatica_pos ? 'true' : 'false' }}); open = false"
                            class="w-full text-left px-5 py-3 hover:bg-indigo-50 transition-colors flex items-center justify-between gap-2 border-b border-slate-50 last:border-0"
                            :class="cajaId === {{ $caja->id }} ? 'bg-indigo-50 border-l-4 border-l-indigo-500' : ''"
                        >
                            <div class="flex flex-col gap-0.5">
                                <span class="text-sm font-black text-slate-800 tracking-tight">{{ $caja->nombre }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $caja->sucursal?->nombre ?? 'Sin sucursal' }}</span>
                            </div>

                            {{-- Estado Visual --}}
                            @if($caja->abierta)
                                <span class="flex h-2 w-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50" title="Caja Abierta"></span>
                            @else
                                <span class="flex h-2 w-2 rounded-full bg-slate-300 border border-slate-100" title="Caja Cerrada"></span>
                            @endif
                        </button>
                    @empty
                        <div class="px-5 py-8 text-center text-slate-400 italic text-sm">No hay cajas configuradas.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- User Avatar --}}
        <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-sm font-black shadow-lg shadow-indigo-600/30 ring-2 ring-indigo-500/20">
            {{ substr(auth('moonshine')->user()->name ?? 'U', 0, 1) }}
        </div>
    </div>

    {{-- ══════════════════════════════════════ --}}
    {{-- MAIN BODY (Responsive Layout)          --}}
    {{-- ══════════════════════════════════════ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ────────────────────────────────── --}}
        {{-- LEFT: Search + Products            --}}
        {{-- ────────────────────────────────── --}}
        <div class="flex flex-col w-1/2 overflow-hidden bg-white border-r border-slate-200 min-w-0">

            {{-- Search Bar (Fixed at top) --}}
            <div class="px-6 py-4 bg-white/70 backdrop-blur-lg border-b border-slate-100 sticky top-0 z-10">
                <div class="relative group">
                    <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        x-ref="searchInput"
                        x-model="query"
                        @input.debounce.200ms="search()"
                        type="text"
                        placeholder="Buscar por NOMBRE, SKU o CÓDIGO..."
                        :disabled="!cajaId"
                        class="w-full pl-12 pr-6 h-12 text-sm font-bold border border-slate-200 rounded-2xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 bg-white/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all placeholder:text-slate-300"
                    >
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="flex-1 overflow-y-auto p-6 scrollbar-thin scrollbar-thumb-slate-200 bg-slate-50">

                <div x-show="loading" class="flex flex-col items-center justify-center py-10 text-slate-400 gap-3 animate-pulse">
                    <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-10" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-xs font-black uppercase tracking-widest">Sincronizando productos...</span>
                </div>

                <div x-show="!loading && cajaId && products.length === 0 && query.length > 0"
                     class="flex flex-col items-center justify-center py-20 text-slate-300 gap-4">
                    <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-bold">No encontramos "<span class="text-slate-800" x-text="query"></span>"</p>
                </div>

                <div x-show="!loading && products.length > 0"
                     class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    <template x-for="(p, i) in products" :key="p.id">
                        <button
                            @click="addItem(p)"
                            class="group relative bg-white border border-slate-200 rounded-2xl p-4 text-left transition-all duration-200 hover:shadow-xl hover:border-indigo-200 active:scale-95"
                        >
                            {{-- Batch/Stock Info --}}
                            <div class="absolute top-2 right-2 z-10">
                                <span class="bg-white/90 backdrop-blur-sm shadow-sm border border-slate-100 text-[9px] font-black px-2 py-0.5 rounded-full text-slate-500 uppercase tracking-tighter"
                                      x-text="p.stock + ' Uds'"></span>
                            </div>

                            <div class="w-full aspect-square rounded-xl mb-3 overflow-hidden flex items-center justify-center relative group-hover:brightness-95 transition-all">
                                <template x-if="p.imagen">
                                    <img :src="baseUrl + 'storage/' + p.imagen" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!p.imagen">
                                    <div
                                        class="w-full h-full flex flex-col items-center justify-center gap-1"
                                        :style="`background: ${['linear-gradient(135deg,#e0e7ff,#c7d2fe)','linear-gradient(135deg,#d1fae5,#a7f3d0)','linear-gradient(135deg,#fce7f3,#fbcfe8)','linear-gradient(135deg,#fef3c7,#fde68a)','linear-gradient(135deg,#dbeafe,#bfdbfe)','linear-gradient(135deg,#ede9fe,#ddd6fe)'][i % 6]}`"
                                    >
                                        <span
                                            class="text-2xl font-black tracking-tighter select-none"
                                            :style="`color: ${['#4f46e5','#059669','#db2777','#d97706','#2563eb','#7c3aed'][i % 6]}`"
                                            x-text="p.nombre.substring(0,2).toUpperCase()"
                                        ></span>
                                        <span class="text-[8px] font-black uppercase tracking-widest opacity-40"
                                              :style="`color: ${['#4f46e5','#059669','#db2777','#d97706','#2563eb','#7c3aed'][i % 6]}`"
                                              x-text="p.sku"
                                        ></span>
                                    </div>
                                </template>
                            </div>

                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest truncate" x-text="p.sku"></p>
                                <p class="text-xs font-bold text-slate-800 leading-tight h-8 line-clamp-2" x-text="p.nombre"></p>
                                <div class="flex items-end justify-between mt-2">
                                    <p class="text-base font-black text-indigo-700 font-mono" x-text="fmt(p.precio)"></p>
                                </div>
                            </div>

                            {{-- Hover Plus Indicator --}}
                            <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/5 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 rounded-2xl pointer-events-none">
                                 <div class="w-10 h-10 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-lg shadow-indigo-600/40">
                                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                 </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- ────────────────────────────────── --}}
        {{-- RIGHT: Cart + Payments (Glassmorphic) --}}
        {{-- ────────────────────────────────── --}}
        <div class="w-1/2 flex flex-col bg-white shrink-0 border-l border-slate-200">

            {{-- Checkout Header --}}
            <div class="px-6 py-4 border-b border-slate-100 bg-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2">
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-700">Mesa de Trabajo</h3>
                            <p class="text-[10px] text-slate-400 font-bold" x-text="cart.length + ' producto(s) en carrito'"></p>
                        </div>
                        <button
                            type="button"
                            x-show="cajaId"
                            @click.prevent="openAuditModal('cierre')"
                            class="flex items-center gap-2 px-3 py-2 bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                            Cerrar Caja
                        </button>
                        <button
                            type="button"
                            x-show="cajaId"
                            @click.prevent="showGastoModal = true"
                            class="flex items-center gap-2 px-3 py-2 bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Gasto
                        </button>
                    </div>
                </div>
                <button @click="cart = []" x-show="cart.length > 0"
                    class="text-[10px] font-black text-rose-400 hover:text-rose-600 transition-colors uppercase tracking-wider flex items-center gap-1 px-3 py-1.5 rounded-lg hover:bg-rose-50">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    Vaciar
                </button>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-2.5 min-h-[300px] lg:min-h-0 bg-slate-50/50">
                <template x-for="(item, idx) in cart" :key="item.id">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-4 py-3.5 flex items-center gap-4 group transition-all hover:shadow-md hover:border-indigo-100">
                        {{-- Número de ítem --}}
                        <div class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-black flex items-center justify-center shrink-0" x-text="idx + 1"></div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-slate-800 leading-tight line-clamp-1" x-text="item.nombre"></p>
                            <p class="text-xs font-bold text-slate-400 mt-0.5" x-text="fmt(item.precio) + ' c/u'"></p>
                        </div>
                        
                        <div class="flex items-center gap-1.5 px-1.5 py-1.5 bg-slate-100 rounded-xl">
                            <button @click="decreaseQty(idx)" class="w-8 h-8 rounded-lg bg-white shadow-sm hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center font-black text-slate-600 text-base transition-all active:scale-90">−</button>
                            <span class="w-8 text-center text-sm font-black tabular-nums text-slate-800" x-text="item.qty"></span>
                            <button @click="increaseQty(idx)" class="w-8 h-8 rounded-lg bg-white shadow-sm hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-center font-black text-slate-600 text-base transition-all active:scale-90">+</button>
                        </div>

                        <div class="text-right min-w-[90px]">
                            <p class="text-sm font-black text-slate-900 tabular-nums" x-text="fmt(item.precio * item.qty)"></p>
                            <button @click="removeItem(idx)" class="text-[10px] font-black text-rose-400 hover:text-rose-600 mt-1 uppercase tracking-wider transition-all">✕ quitar</button>
                        </div>
                    </div>
                </template>

                {{-- Empty Cart --}}
                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-56 text-slate-300 gap-5 border-2 border-dashed border-slate-200 rounded-3xl bg-white/50 mx-1">
                     <div class="p-5 bg-slate-100 rounded-2xl">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                     </div>
                     <div class="text-center">
                         <p class="font-black text-xs uppercase tracking-widest opacity-50">Carrito vacío</p>
                         <p class="text-[10px] text-slate-300 mt-1">Selecciona un producto del catálogo</p>
                     </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════ --}}
    {{-- BARRA DE COBRO — Full Width Bottom Bar                            --}}
    {{-- ══════════════════════════════════════════════════════════════════ --}}
    <div class="shrink-0 bg-white border-t border-slate-200 shadow-[0_-8px_30px_-8px_rgba(0,0,0,0.08)] z-10">

        {{-- Error Banner (aparece encima de la barra si hay error) --}}
        <div x-show="errorMsg"
             x-transition:enter="transition duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="px-6 py-2.5 bg-rose-50 border-b border-rose-100 flex items-center gap-3 text-rose-700">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-xs font-bold leading-tight flex-1" x-text="errorMsg"></p>
            <button @click="errorMsg = ''" class="text-rose-400 hover:text-rose-600 ml-auto shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- La barra principal de cobro: LEFT flex | RIGHT fixed (COBRAR) --}}
        <div class="flex items-stretch min-h-[68px]">

            {{-- ── ZONA IZQUIERDA: todos los controles sin overflow ── --}}
            <div class="flex-1 flex items-center gap-2 px-4 py-2 min-w-0">

                {{-- Método de Cobro --}}
                <div class="flex flex-col gap-0.5 shrink-0">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Método</p>
                    <div class="flex gap-0.5 p-0.5 bg-slate-100 rounded-xl items-center">
                        <button @click="metodo = 'efectivo'"
                                :class="metodo === 'efectivo' ? 'bg-white shadow text-indigo-600' : 'text-slate-500 hover:bg-slate-200/50'"
                                class="px-2.5 py-1.5 text-[10px] font-black rounded-[10px] transition-all flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            EFECTIVO
                        </button>
                        <button @click="metodo = 'tarjeta'; recibido = ''"
                                :class="metodo === 'tarjeta' ? 'bg-white shadow text-indigo-600' : 'text-slate-500 hover:bg-slate-200/50'"
                                class="px-2.5 py-1.5 text-[10px] font-black rounded-[10px] transition-all flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            TARJETA
                        </button>
                    </div>
                </div>

                <div class="w-px h-8 bg-slate-200 shrink-0"></div>

                {{-- Total a Pagar --}}
                <div class="flex flex-col gap-0 shrink-0">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Total Cobro</p>
                    <span class="text-2xl font-black text-indigo-600 font-mono tracking-tighter leading-none" x-text="fmt(total())"></span>
                </div>

                {{-- EFECTIVO RECIBIDO + VUELTO — solo en modo efectivo --}}
                <template x-if="metodo === 'efectivo'">
                    <div class="flex items-center gap-2 shrink-0">

                        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

                        {{-- Bloque efectivo: ancho fijo para que los billetes no desborden --}}
                        <div class="flex flex-col gap-1 shrink-0 w-36">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Efectivo Recibido</p>
                            {{-- Input --}}
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">{{ get_currency_symbol() }}</span>
                                <input
                                    x-ref="recibidoInput"
                                    x-model="recibido"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    :class="vueltoNegativo() ? 'border-rose-300 focus:border-rose-500 bg-rose-50' : 'border-slate-200 focus:border-indigo-400 bg-white'"
                                    class="w-full pl-6 pr-2 h-8 text-sm font-black border-2 rounded-xl focus:outline-none transition-all tabular-nums"
                                >
                            </div>
                            {{-- Billetes: texto abreviado sin decimales, caben dentro de w-36 --}}
                            <div class="flex gap-1">
                                <template x-for="bill in quickBills()" :key="bill">
                                    <button
                                        @click="setRecibido(bill)"
                                        :class="parseFloat(recibido) === bill ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-400 hover:text-indigo-600'"
                                        class="flex-1 text-[9px] font-black py-0.5 rounded-lg border transition-all leading-tight"
                                        x-text="'$' + bill"
                                    ></button>
                                </template>
                            </div>
                        </div>

                        <div class="w-px h-8 bg-slate-200 shrink-0"></div>

                            {{-- Falta --}}
                            <span x-show="vueltoNegativo()"
                                class="text-[9px] font-black text-rose-400 leading-none"
                                x-text="'Falta ' + fmt(total() - (parseFloat(recibido)||0))"
                            ></span>
                        </div>

                        {{-- Desglose del Vuelto Assistant --}}
                        <div x-show="vuelto() > 0" class="flex flex-col gap-1 ml-2 max-w-[150px]">
                            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest leading-none">Entregar:</p>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="item in getVueltoBreakdown(vuelto())" :key="item.label">
                                    <span :class="item.isCoin ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-indigo-50 text-indigo-600 border-indigo-100'"
                                          class="text-[8px] font-black px-1.5 py-0.5 rounded border flex gap-1 items-center">
                                          <span x-text="item.count + 'x'"></span>
                                          <span x-text="item.label"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                    </div>
                </template>

            </div>

            {{-- ── ZONA DERECHA: botón COBRAR siempre visible ── --}}
            <div class="shrink-0 flex items-center px-4 py-2 border-l border-slate-200">
                <button
                    @click="processSale()"
                    :disabled="processing || cart.length === 0 || !cajaId"
                    class="h-11 px-7 bg-slate-900 hover:bg-indigo-600 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-black text-sm rounded-2xl transition-all active:scale-95 shadow-lg flex items-center gap-2 tracking-widest relative overflow-hidden group"
                >
                    <div class="absolute inset-0 bg-indigo-400/0 group-hover:bg-indigo-400/10 transition-all rounded-2xl"></div>
                    <span x-show="!processing" class="flex items-center gap-2 relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        COBRAR
                        <span class="text-[10px] opacity-50 border border-white/20 px-1.5 py-0.5 rounded-md font-bold">F5</span>
                    </span>
                    <div x-show="processing" class="flex items-center gap-2 relative">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span class="text-xs">PROCESANDO...</span>
                    </div>
                </button>
            </div>

        </div>
    </div>




    {{-- ══════════════════════════════════════ --}}
    {{-- SUCCESS MODAL                          --}}
    {{-- ══════════════════════════════════════ --}}
    <div x-show="successData"
         class="fixed inset-0 bg-slate-900/80 flex items-center justify-center z-50 p-4 backdrop-blur-xl"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-sm p-5 text-center space-y-4 relative flex flex-col max-h-[95vh] overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200" 
             @click.stop
             x-transition:enter="transition transform duration-500"
             x-transition:enter-start="scale-50 translate-y-20 rotate-6"
             x-transition:enter-end="scale-100 translate-y-0 rotate-0">
             
            <div class="w-16 h-16 bg-emerald-500 rounded-[24px] flex items-center justify-center mx-auto shadow-xl shadow-emerald-500/30 rotate-12 shrink-0">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <div class="space-y-1">
                <h2 class="text-xl font-black text-slate-900 tracking-tighter">¡Venta Realizada!</h2>
                <div class="inline-flex px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-[9px] font-black uppercase tracking-widest">
                    Ticket #<span x-text="String(successData?.id ?? 0).padStart(6, '0')"></span>
                </div>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Cobrado</p>
                    <p class="text-3xl font-black text-slate-900 tabular-nums" x-text="fmt(successData?.total ?? 0)"></p>
                </div>
                {{-- Vuelto en el modal (solo si aplica) --}}
                <template x-if="successData?.vuelto !== null && successData?.vuelto !== undefined && successData?.recibido > 0">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                            <div class="text-left">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Recibido</p>
                                <p class="text-base font-black text-slate-600 tabular-nums" x-text="fmt(successData?.recibido ?? 0)"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Vuelto</p>
                                <p class="text-xl font-black text-emerald-500 tabular-nums" x-text="fmt(successData?.vuelto ?? 0)"></p>
                            </div>
                        </div>
                        
                        {{-- Desglose en Éxito --}}
                        <div class="pt-2 border-t border-slate-100">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 text-left">Desglose de cambio:</p>
                            <div class="grid grid-cols-2 gap-1.5">
                                <template x-for="item in getVueltoBreakdown(successData?.vuelto)" :key="item.label">
                                    <div :class="item.isCoin ? 'bg-emerald-50 border-emerald-100' : 'bg-indigo-50 border-indigo-100'"
                                         class="flex items-center justify-between px-2 py-1.5 rounded-xl border">
                                        <span class="text-[9px] font-black text-slate-500" x-text="item.label"></span>
                                        <span :class="item.isCoin ? 'bg-emerald-500' : 'bg-indigo-500'" 
                                              class="w-5 h-5 rounded-lg text-white text-[9px] font-black flex items-center justify-center shadow-sm" 
                                              x-text="item.count"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex flex-col gap-2 shrink-0">
                <button
                    @click="printTicket()"
                    class="w-full py-3 bg-slate-900 rounded-[15px] text-xs font-black text-white hover:bg-slate-800 transition-all active:scale-95 shadow-lg flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 012-2H9a2 2 0 012 2v4a2 2 0 01-2 2zM17 11V7a2 2 0 00-2-2H9a2 2 0 00-2 2v4h8z"/></svg>
                    TICKET
                </button>
                <button
                    type="button"
                    @click="closeSuccess()"
                    class="w-full py-2.5 bg-white border border-slate-200 rounded-[15px] text-xs font-black text-slate-500 hover:bg-slate-50 transition-all"
                >Nueva Venta</button>
            </div>
        </div>
    </div>

    {{-- MODAL DE AUDITORÍA (APERTURA / CIERRE) --}}
    <div x-show="showAuditModal" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
    >
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md flex flex-col max-h-[90vh] overflow-hidden border border-slate-100">
            {{-- Header (Fijo) --}}
            <div class="p-6 bg-slate-50 border-b border-slate-100 flex items-center gap-4 shrink-0">
                <div :class="auditType === 'apertura' ? 'bg-indigo-500' : 'bg-rose-500'" 
                     class="p-3 rounded-2xl shadow-lg shadow-indigo-500/20">
                    <svg x-show="auditType === 'apertura'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <svg x-show="auditType === 'cierre'" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 10h6m-6 4h6"/></svg>
                </div>
                <div>
                    <h3 class="font-black text-slate-800 uppercase tracking-tight" x-text="auditType === 'apertura' ? 'Arqueo de Apertura' : 'Cerrar Jornada (Arqueo)'"></h3>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest" x-text="auditType === 'apertura' ? 'Ingresa el efectivo inicial' : 'Ingresa el efectivo total contado'"></p>
                </div>
            </div>

            {{-- Body (Scrollable) --}}
            <div class="p-6 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200">
                
                {{-- Shift Summary (Only for Cierre) --}}
                <div x-show="auditType === 'cierre'" class="mb-6">
                    <div x-show="isSummaryLoading" class="flex flex-col items-center justify-center py-4 text-slate-400 gap-2 animate-pulse bg-slate-50 rounded-2xl border border-slate-100 italic">
                        <span class="text-[9px] font-black uppercase tracking-widest">Calculando balances...</span>
                    </div>
                    
                    <div x-show="!isSummaryLoading" class="p-4 bg-slate-900 rounded-2xl shadow-xl border border-slate-800">
                        <h4 class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-3 border-b border-white/5 pb-2">Estado de Caja Actual</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center text-slate-300">
                                <span class="font-medium opacity-60">Ventas (+)</span>
                                <span class="font-black text-emerald-400" x-text="fmt(summary.ventas)"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-300">
                                <span class="font-medium opacity-60">Gastos (-)</span>
                                <span class="font-black text-rose-400" x-text="'- ' + fmt(summary.egresos)"></span>
                            </div>
                            <div class="flex justify-between items-center text-slate-300">
                                <span class="font-medium opacity-60">Fondo Inicial</span>
                                <span class="font-bold opacity-80" x-text="fmt(summary.apertura)"></span>
                            </div>
                            <div class="pt-2 mt-2 border-t border-white/10 flex justify-between items-center">
                                <span class="text-[9px] font-black text-indigo-400 uppercase">Debes Entregar:</span>
                                <span class="text-lg font-black text-white font-mono tracking-tighter" x-text="fmt(summary.esperado)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Efectivo Físico en Caja</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-slate-400 font-black text-lg">{{ get_currency_symbol() }}</span>
                    </div>
                    <input 
                        type="number" 
                        step="0.01"
                        x-model="auditMonto"
                        class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-2xl font-black text-slate-800 focus:border-indigo-500 focus:bg-white transition-all outline-none"
                        placeholder="0.00"
                        autofocus
                    >
                </div>

                {{-- Calculadora de Denominaciones --}}
                <div class="mt-6 border-t border-slate-100 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Calculadora de Arqueo</span>
                        <button @click="billCounts = {}; updateAuditFromCalculator()" class="text-[9px] font-black text-rose-400 uppercase tracking-widest hover:text-rose-600 transition-colors">Limpiar</button>
                    </div>

                    <div class="space-y-6">
                        {{-- Sección Billetes --}}
                        <div>
                            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Billetes
                            </p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <template x-for="d in denominations.filter(v => v > 1)" :key="d">
                                    <div class="flex items-center gap-2">
                                        <div class="w-12 text-right">
                                            <span class="text-[10px] font-black text-slate-500" x-text="fmt(d)"></span>
                                        </div>
                                        <div class="flex-1 relative">
                                            <input 
                                                type="number" 
                                                min="0"
                                                x-model="billCounts[d]"
                                                @input="updateAuditFromCalculator()"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-black text-slate-800 focus:border-indigo-400 focus:bg-white transition-all outline-none"
                                                placeholder="0"
                                            >
                                            <div class="absolute -top-2 -right-1" x-show="billCounts[d] > 0">
                                                <span class="bg-indigo-500 text-white text-[8px] px-1 rounded-full font-black shadow-sm" x-text="fmt(d * billCounts[d])"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Sección Monedas --}}
                        <div>
                            <p class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Monedas
                            </p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <template x-for="d in denominations.filter(v => v <= 1)" :key="d">
                                    <div class="flex items-center gap-2">
                                        <div class="w-12 text-right">
                                            <span class="text-[10px] font-black text-slate-500" x-text="fmt(d)"></span>
                                        </div>
                                        <div class="flex-1 relative">
                                            <input 
                                                type="number" 
                                                min="0"
                                                x-model="billCounts[d]"
                                                @input="updateAuditFromCalculator()"
                                                class="w-full px-3 py-2 bg-emerald-50/30 border border-slate-200 rounded-xl text-xs font-black text-slate-800 focus:border-emerald-400 focus:bg-white transition-all outline-none"
                                                placeholder="0"
                                            >
                                            <div class="absolute -top-2 -right-1" x-show="billCounts[d] > 0">
                                                <span class="bg-emerald-500 text-white text-[8px] px-1 rounded-full font-black shadow-sm" x-text="fmt(d * billCounts[d])"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p x-show="auditType === 'apertura'" class="mt-4 text-[10px] text-slate-400 font-bold leading-relaxed px-2">
                    * El sistema comparará este monto con el fondo asignado de 
                    <span class="text-indigo-500">{{ format_currency(get_global_setting('default_opening_amount', 50)) }}</span>.
                </p>
                <p x-show="auditType === 'cierre'" class="mt-4 text-[10px] text-slate-400 font-bold leading-relaxed px-2">
                    * Asegúrate de que el total coincida con el dinero físico en gaveta. Las diferencias serán registradas automáticamente.
                </p>
            </div>

            {{-- Footer (Fijo) --}}
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center gap-3 shrink-0">
                <button @click="showAuditModal = false" 
                        class="flex-1 px-4 py-3 text-slate-500 font-black text-xs uppercase tracking-widest hover:text-slate-800 transition-colors">
                    Cancelar
                </button>
                <button @click="submitAudit()" 
                        :disabled="isAuditLoading"
                        :class="auditType === 'apertura' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-rose-600 hover:bg-rose-700'"
                        class="flex-[2] py-4 text-white font-black text-xs uppercase tracking-[0.2em] rounded-2xl shadow-xl transition-all active:scale-95 flex items-center justify-center gap-2">
                    <svg x-show="isAuditLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span x-text="isAuditLoading ? 'PROCESANDO...' : (auditType === 'apertura' ? 'INICIAR JORNADA' : 'CERRAR Y AUDITAR')"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════ --}}
    {{-- MODAL DE GASTO                         --}}
    {{-- ══════════════════════════════════════ --}}
    <div 
        x-show="showGastoModal" 
        x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden ring-1 ring-slate-200"
             x-transition:enter="transition transform duration-300"
             x-transition:enter-start="scale-95 -translate-y-4"
             x-transition:enter-end="scale-100 translate-y-0"
        >
            <div class="px-8 py-6 bg-gradient-to-br from-amber-500 to-amber-600 text-white relative">
                <h3 class="text-xl font-black uppercase tracking-tight">Registrar Gasto</h3>
                <p class="text-sm text-amber-100 font-medium">Extraer dinero de la caja activa</p>
                <button type="button" @click="showGastoModal = false" class="absolute top-4 right-4 p-2 hover:bg-white/10 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Monto del Gasto</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-black">{{ get_currency_symbol() }}</span>
                        <input 
                            type="number"
                            id="gasto-monto-input"
                            x-model="gastoMonto"
                            class="w-full pl-10 pr-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-amber-500 focus:ring-0 transition-all font-black text-2xl"
                            placeholder="0.00"
                            min="0.01"
                            step="0.01"
                        >
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-500">Descripción / Motivo</label>
                    <textarea 
                        x-model="gastoDescripcion"
                        class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-amber-500 focus:ring-0 transition-all font-medium text-slate-700 min-h-[100px] resize-none"
                        placeholder="Ej: Compra de artículos de limpieza..."
                    ></textarea>
                </div>

                <div x-show="errorMsg" class="px-4 py-3 bg-rose-50 border border-rose-100 rounded-2xl text-xs font-bold text-rose-700" x-text="errorMsg"></div>

                <div class="flex gap-3 pt-2">
                    <button 
                        type="button"
                        @click="showGastoModal = false; gastoMonto = 0; gastoDescripcion = ''"
                        class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-slate-200 transition-all"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button"
                        @click="submitGasto()"
                        class="flex-[2] py-4 bg-amber-500 text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-amber-600 shadow-lg shadow-amber-500/30 transition-all"
                    >
                        Registrar Gasto
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function pos() {
    return {
        baseUrl:     '{{ asset("/") }}',
        cajaId:      null,
        cajaLocaleId: null,
        cajaNombre:  '',
        query:       '',
        products:    [],
        cart:        [],
        metodo:      'efectivo',
        recibido:    '',
        loading:     false,
        processing:  false,
        errorMsg:    '',
        successData: null,
        
        // Audit System
        showAuditModal: false,
        auditType: '',
        auditMonto: 0,
        auditObservacion: '',

        // GASTO MODAL
        showGastoModal: false,
        gastoDescripcion: '',
        isAuditLoading: false,
        isSummaryLoading: false,

        // SUMMARY DATA
        summary: { ventas: 0, egresos: 0, apertura: 0, esperado: 0 },

        // CALCULADORA DE DENOMINACIONES
        denominations: [100, 50, 20, 10, 5, 1, 0.25, 0.10, 0.05, 0.01],
        billCounts: {},

        updateAuditFromCalculator() {
            let total = 0;
            this.denominations.forEach(d => {
                total += (parseFloat(d) * (parseInt(this.billCounts[d]) || 0));
            });
            this.auditMonto = total.toFixed(2);
        },

        getVueltoBreakdown(amount) {
            if (!amount || amount <= 0) return [];
            
            // Trabajamos con centavos (enteros) para evitar errores de precisión de JS
            let remainingCents = Math.round(parseFloat(amount) * 100);
            let breakdown = [];
            
            // Ordenar de mayor a menor
            const sortedDenoms = [...this.denominations].sort((a,b) => b - a);
            
            for (const d of sortedDenoms) {
                const dCents = Math.round(parseFloat(d) * 100);
                const count = Math.floor(remainingCents / dCents);
                
                if (count > 0) {
                    breakdown.push({ label: this.fmt(d), val: d, count: count, isCoin: d < 1 });
                    remainingCents -= count * dCents;
                }
            }
            return breakdown;
        },

        total() {
            return this.cart.reduce((s, i) => s + i.precio * i.qty, 0);
        },

        vuelto() {
            const r = parseFloat(this.recibido) || 0;
            const t = this.total();
            return r >= t ? r - t : null;
        },

        vueltoNegativo() {
            const r = parseFloat(this.recibido) || 0;
            return r > 0 && r < this.total();
        },

        setRecibido(monto) {
            this.recibido = monto;
            this.$nextTick(() => {
                if (this.$refs.recibidoInput) this.$refs.recibidoInput.focus();
            });
        },

        fmt(v) {
            return '{{ get_currency_symbol() }} ' + parseFloat(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        init() {
            this.$nextTick(() => {
                if (this.$refs.searchInput) this.$refs.searchInput.focus();
            });
        },

        selectCaja(id, nombre, localeId, abierta = true, autoOpen = false) {
            this.cajaId       = id;
            this.cajaNombre   = nombre;
            this.cajaLocaleId = localeId;
            this.cart         = [];
            this.query        = '';
            this.products     = [];
            this.errorMsg     = '';
            this.recibido     = '';
            this.billCounts   = {};
            this.auditMonto   = 0;

            if (!abierta) {
                if (autoOpen) {
                    this.autoAbrirCaja();
                } else {
                    this.openAuditModal('apertura');
                }
            }

            this.init();
        },

        async openAuditModal(type) {
            this.auditType = type;
            this.auditMonto = 0;
            this.billCounts = {};
            this.errorMsg = '';
            
            if (type === 'cierre') {
                await this.fetchSummary();
            } else if (type === 'apertura') {
                this.auditMonto = {{ get_global_setting('default_opening_amount', 50) }};
            }
            
            this.showAuditModal = true;
        },

        async fetchSummary() {
            if (!this.cajaId) return;
            this.isSummaryLoading = true;
            try {
                const res = await fetch(`${this.baseUrl}cajas/${this.cajaId}/summary`);
                if (res.ok) {
                    this.summary = await res.json();
                    this.auditMonto = 0; // Reset count
                }
            } catch (e) {
                console.error('Error fetching summary:', e);
            } finally {
                this.isSummaryLoading = false;
            }
        },

        async autoAbrirCaja() {
            const montoDefault = {{ get_global_setting('default_opening_amount', 50) }};
            try {
                const fd = new FormData();
                fd.append('monto_apertura', montoDefault);
                fd.append('monto_esperado', montoDefault);

                const res = await fetch(`${this.baseUrl}cajas/${this.cajaId}/abrir`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: fd
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && data.success) {
                    this.search(); // Habilitar búsqueda de productos
                } else {
                    this.flashError(data.error || 'No se pudo abrir la caja automáticamente.');
                }
            } catch (e) {
                this.flashError('Error al intentar abrir la caja automáticamente.');
            }
        },

        async search() {
            if (!this.cajaId) return;
            this.loading     = true;
            try {
                const res  = await fetch(`${this.baseUrl}pos/search?query=${encodeURIComponent(this.query)}&caja_id=${this.cajaId}`);
                const data = await res.json();
                
                if (res.status === 422 && data.error && (data.error.includes('cerrada') || data.error.includes('abierta'))) {
                    this.openAuditModal('apertura');
                    return;
                }

                this.products = data.error ? [] : data;
                
                if (this.products.length === 1 && this.query.trim().length > 3) {
                    const p = this.products[0];
                    const qRaw = this.query.trim().toLowerCase();
                    if (p.codigo_barra?.toLowerCase() === qRaw || p.sku?.toLowerCase() === qRaw) {
                        this.addItem(p);
                        this.query = '';
                    }
                }
            } catch (e) { this.products = []; } 
            finally { this.loading = false; }
        },

        addItem(p) {
            const existing = this.cart.find(i => i.id === p.id);
            if (existing) {
                if (existing.qty < p.stock) existing.qty++;
                else this.flashError(`Stock máximo alcanzado (${p.stock})`);
            } else {
                this.cart.push({ id: p.id, nombre: p.nombre, precio: p.precio, qty: 1, stock: p.stock });
            }
            this.errorMsg = '';
            if (this.$refs.scanSound) this.$refs.scanSound.play().catch(() => {});
            this.init();
        },

        increaseQty(idx) {
            if (this.cart[idx].qty < this.cart[idx].stock) this.cart[idx].qty++;
            else this.flashError('Inventario insuficiente.');
        },

        decreaseQty(idx) {
            if (this.cart[idx].qty > 1) this.cart[idx].qty--;
            else this.removeItem(idx);
        },

        removeItem(idx) { this.cart.splice(idx, 1); },

        flashError(msg) {
            this.errorMsg = msg;
            setTimeout(() => { if(this.errorMsg === msg) this.errorMsg = ''; }, 8000);
        },

        async processSale() {
            if (this.processing || this.cart.length === 0 || !this.cajaId) return;
            this.processing = true;
            this.errorMsg   = '';
            
            try {
                const res = await fetch(`${this.baseUrl}pos/store`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        caja_id:     this.cajaId,
                        metodo_pago: this.metodo,
                        items: this.cart.map(i => ({ id: i.id, cantidad: i.qty, precio: i.precio })),
                    }),
                });

                const data = await res.json();
                
                if (res.status === 422) {
                    const errors = data.errors ? Object.values(data.errors).flat().join(' | ') : (data.error || 'Validación fallida.');
                    this.flashError(errors);
                    return;
                }

                if (data.success) {
                    const totalSrv = parseFloat(data.total);
                    const recibidoNum = parseFloat(this.recibido) || 0;
                    // Recalcular vuelto basado en el total exacto procesado por el servidor
                    const vueltoReal = recibidoNum > totalSrv ? (recibidoNum - totalSrv).toFixed(2) : 0;
                    
                    this.successData = { 
                        id: data.venta_id, 
                        total: totalSrv, 
                        vuelto: vueltoReal, 
                        recibido: recibidoNum 
                    };
                    
                    this.cart = [];
                    this.query = '';
                    this.products = [];
                } else {
                    this.flashError(data.error || 'Error en el servidor.');
                }
            } catch (e) {
                this.flashError('Error de red crítico.');
            } finally {
                this.processing = false;
            }
        },

        async submitGasto() {
            if (!this.cajaId) return;
            if (this.gastoMonto <= 0) {
                this.errorMsg = 'El monto del gasto debe ser mayor a 0.';
                return;
            }

            try {
                const url = `${this.baseUrl}cajas/${this.cajaId}/egreso`;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        monto: this.gastoMonto,
                        descripcion_libre: this.gastoDescripcion
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showGastoModal = false;
                    this.gastoMonto = 0;
                    this.gastoDescripcion = '';
                    // Notificación local si existe
                    alert('Gasto registrado con éxito.');
                } else {
                    this.errorMsg = data.error || 'Error al registrar el gasto.';
                }
            } catch (error) {
                console.error('Error:', error);
                this.errorMsg = 'Error de conexión al registrar el gasto.';
            }
        },

        closeSuccess() {
            this.successData = null;
            this.recibido    = '';
            this.init();
        },

        quickBills() {
            const t = this.total();
            if (t <= 0) return [];
            // Genera 4 billetes sugeridos: exacto, y redondeos hacia arriba
            const denominations = [1, 5, 10, 20, 50, 100, 200, 500, 1000];
            const bills = new Set();
            // Redondear al billete más cercano por encima
            for (const d of denominations) {
                const rounded = Math.ceil(t / d) * d;
                if (rounded >= t) bills.add(rounded);
                if (bills.size >= 5) break;
            }
            const result = [...bills].sort((a, b) => a - b);
            return result.slice(0, 4);
        },

        printTicket() {
            if (!this.successData) return;
            window.open(`${this.baseUrl}pos/ticket/${this.successData.id}`, '_blank', 'width=400,height=600');
        },

        handleKey(e) {
            if (e.key === 'F5') { e.preventDefault(); this.processSale(); }
            if (e.key === 'F2') { e.preventDefault(); this.init(); }
            if (e.key === 'Escape') {
                if (this.successData) { this.closeSuccess(); }
                else if (this.showAuditModal) { this.showAuditModal = false; }
                else if (this.showGastoModal) { this.showGastoModal = false; }
                else { this.query = ''; this.products = []; this.init(); }
            }
        },

        async submitAudit() {
            if (this.isAuditLoading) return;
            this.isAuditLoading = true;

            const url = this.auditType === 'apertura'
                ? `${this.baseUrl}cajas/${this.cajaId}/abrir`
                : `${this.baseUrl}cajas/${this.cajaId}/cerrar`;

            const field = this.auditType === 'apertura' ? 'monto_apertura' : 'monto_real';

            try {
                const fd = new FormData();
                fd.append(field, this.auditMonto);
                if (this.auditType === 'apertura') {
                    fd.append('monto_esperado', {{ get_global_setting('default_opening_amount', 50) }});
                }

                // Enviar desglose de denominaciones
                for (const d in this.billCounts) {
                    if (this.billCounts[d] > 0) {
                        fd.append(`denominaciones[${d}]`, this.billCounts[d]);
                    }
                }

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: fd
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && data.success) {
                    this.showAuditModal = false;
                    this.auditMonto = 0;
                    if (this.auditType === 'apertura') {
                        this.search(); // Habilitar búsqueda de productos
                    } else {
                        location.reload(); // Resetear estado tras cierre
                    }
                } else {
                    const errMsg = data.error || data.message || 'Error al procesar la operación de caja.';
                    this.flashError(errMsg);
                }
            } catch (e) {
                this.flashError('Error de conexión al procesar la auditoría.');
            } finally {
                this.isAuditLoading = false;
            }
        }
    };
}
</script>

<style>
    [x-cloak] { display: none !important; }
    .scrollbar-thin::-webkit-scrollbar { width: 4px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    /* CSS de Respaldo - Ocultación total de nombres en menú colapsado */
    .layout-menu._is-minimized .menu-text,
    .layout-menu._is-minimized .menu-arrow,
    .layout-menu._is-minimized span:not(.menu-icon) {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
        width: 0 !important;
        height: 0 !important;
        position: absolute !important;
    }

    .layout-menu._is-minimized .menu-link {
        justify-content: center !important;
        padding: 0.75rem 0 !important;
    }

    .layout-menu._is-minimized .menu-inner-item {
        width: 2.75rem !important;
        height: 2.75rem !important;
        margin: 0.5rem auto !important;
    }
    /* Maximizando el área de trabajo POS - SOLO EN ESCRITORIO */
    @media (min-width: 1024px) {
        .layout-page,
        .layout-main,
        .layout-main-centered,
        .layout-content,
        .layout-wrapper,
        #_moonshine-content,
        .container-fluid,
        .container {
            max-width: none !important;
            width: 100% !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }
        
        .layout-main-centered {
            margin: 0 !important;
        }
    }

</style>

