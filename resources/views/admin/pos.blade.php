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
                
                @php $cajasAbiertas = \App\Models\Caja::where('abierta', 1)->with('sucursal')->get(); @endphp
                <div class="max-h-80 overflow-y-auto py-2">
                    @forelse($cajasAbiertas as $caja)
                        <button
                            @click="selectCaja({{ $caja->id }}, '{{ addslashes($caja->nombre) }}', {{ $caja->locale_id }}); open = false"
                            class="w-full text-left px-5 py-3 hover:bg-indigo-50 transition-colors flex flex-col gap-0.5 border-b border-slate-50 last:border-0"
                            :class="cajaId === {{ $caja->id }} ? 'bg-indigo-50 border-l-4 border-l-indigo-500' : ''"
                        >
                            <span class="text-sm font-black text-slate-800 tracking-tight">{{ $caja->nombre }}</span>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $caja->sucursal?->nombre ?? 'Sin sucursal' }}</span>
                        </button>
                    @empty
                        <div class="px-5 py-8 text-center text-slate-400 italic text-sm">No hay cajas abiertas.</div>
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
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-700">Mesa de Trabajo</h3>
                        <p class="text-[10px] text-slate-400 font-bold" x-text="cart.length + ' producto(s) en carrito'"></p>
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

                        {{-- Vuelto: ancho fijo --}}
                        <div class="flex flex-col gap-0 shrink-0 w-24">
                            <p class="text-[8px] font-black uppercase tracking-widest leading-none"
                               :class="vueltoNegativo() ? 'text-rose-400' : 'text-emerald-500'">Vuelto</p>
                            <span
                                class="text-xl font-black tabular-nums leading-tight transition-all"
                                :class="vuelto() !== null ? 'text-emerald-500' : (vueltoNegativo() ? 'text-rose-500' : 'text-slate-300')"
                                x-text="vuelto() !== null ? fmt(vuelto()) : '—'"
                            ></span>
                            {{-- Falta --}}
                            <span x-show="vueltoNegativo()"
                                class="text-[9px] font-black text-rose-400 leading-none"
                                x-text="'Falta ' + fmt(total() - (parseFloat(recibido)||0))"
                            ></span>
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
        <div class="bg-white rounded-[40px] shadow-2xl w-full max-w-sm p-10 text-center space-y-8 relative overflow-hidden" 
             @click.stop
             x-transition:enter="transition transform duration-500"
             x-transition:enter-start="scale-50 translate-y-20 rotate-6"
             x-transition:enter-end="scale-100 translate-y-0 rotate-0">
            
            <div class="w-24 h-24 bg-emerald-500 rounded-[30px] flex items-center justify-center mx-auto shadow-2xl shadow-emerald-500/40 rotate-12 transition-transform hover:rotate-0 duration-500">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            
            <div class="space-y-2">
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">¡Venta Realizada!</h2>
                <div class="inline-flex px-4 py-1.5 bg-indigo-50 text-indigo-700 rounded-full text-xs font-black uppercase tracking-widest">
                    Ticket #<span x-text="String(successData?.id ?? 0).padStart(6, '0')"></span>
                </div>
            </div>

            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-3">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Cobrado</p>
                    <p class="text-5xl font-black text-slate-900 tabular-nums" x-text="fmt(successData?.total ?? 0)"></p>
                </div>
                {{-- Vuelto en el modal (solo si aplica) --}}
                <template x-if="successData?.vuelto !== null && successData?.vuelto !== undefined && successData?.recibido > 0">
                    <div class="flex items-center justify-between pt-3 border-t border-slate-200">
                        <div class="text-left">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Recibido</p>
                            <p class="text-lg font-black text-slate-600 tabular-nums" x-text="fmt(successData?.recibido ?? 0)"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Vuelto</p>
                            <p class="text-3xl font-black text-emerald-500 tabular-nums" x-text="fmt(successData?.vuelto ?? 0)"></p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex flex-col gap-3">
                <button
                    @click="printTicket()"
                    class="w-full py-4 bg-slate-900 rounded-[20px] text-sm font-black text-white hover:bg-slate-800 transition-all active:scale-95 shadow-xl shadow-slate-900/10 flex items-center justify-center gap-3"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2-2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 012-2H9a2 2 0 012 2v4a2 2 0 01-2 2zM17 11V7a2 2 0 00-2-2H9a2 2 0 00-2 2v4h8z"/></svg>
                    IMPRIMIR TICKET
                </button>
                <button
                    @click="closeSuccess()"
                    class="w-full py-4 bg-white border-2 border-slate-100 rounded-[20px] text-sm font-black text-slate-500 hover:bg-slate-50 transition-all hover:text-slate-800"
                >Nueva Venta</button>
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

        selectCaja(id, nombre, localeId) {
            this.cajaId      = id;
            this.cajaNombre  = nombre;
            this.cajaLocaleId = localeId;
            this.cart        = [];
            this.query       = '';
            this.products    = [];
            this.errorMsg    = '';
            this.recibido    = '';
            this.init();
        },

        async search() {
            if (!this.cajaId) return;
            this.loading     = true;
            try {
                const res  = await fetch(`${this.baseUrl}admin/pos/search?query=${encodeURIComponent(this.query)}&caja_id=${this.cajaId}`);
                const data = await res.json();
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
                const res = await fetch(`${this.baseUrl}admin/pos/store`, {
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
                    this.successData = { id: data.venta_id, total: data.total, vuelto: this.vuelto(), recibido: parseFloat(this.recibido) || 0 };
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
            window.open(`${this.baseUrl}admin/pos/ticket/${this.successData.id}`, '_blank', 'width=400,height=600');
        },

        handleKey(e) {
            if (e.key === 'F5') { e.preventDefault(); this.processSale(); }
            if (e.key === 'Escape') {
                if (this.successData) { this.closeSuccess(); }
                else { this.query = ''; this.products = []; }
            }
        },
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

