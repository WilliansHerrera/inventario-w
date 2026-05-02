<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<aside 
    class="fixed inset-y-0 left-0 z-50 transition-all duration-300 bg-white dark:bg-[#0a0a0a] border-r border-gray-100 dark:border-white/5 flex flex-col"
    :class="sidebarOpen ? 'w-72' : 'w-20'"
>
    <!-- Brand / Logo -->
    <div class="h-20 flex items-center px-6 mb-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white shadow-lg shadow-brand-primary/20">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" /></svg>
            </div>
            <span x-show="sidebarOpen" x-transition.opacity class="font-black text-xl tracking-tighter text-gray-900 dark:text-white uppercase">Inventario-W</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 space-y-2 overflow-y-auto py-4 custom-scrollbar">
        <!-- Dashboard -->
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="m10 19l-7-7m0 0l7-7m-7 7h18" label="Dashboard" />
        
        <div class="pt-6 pb-2 px-4" x-show="sidebarOpen">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Operaciones</span>
        </div>

        <x-sidebar-link :href="route('pos.index')" :active="request()->routeIs('pos.index')" icon="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" label="Punto de Venta" />
        <x-sidebar-link :href="route('cash.index')" :active="request()->routeIs('cash.index')" icon="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" label="Control de Cajas" />
        
        <div class="pt-6 pb-2 px-4" x-show="sidebarOpen">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Inventario</span>
        </div>

        <x-sidebar-link :href="route('inventory.index')" :active="request()->routeIs('inventory.index')" icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" label="Stock de Productos" />
        <x-sidebar-link :href="route('purchases.index')" :active="request()->routeIs('purchases.index')" icon="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" label="Compras / Entradas" />

        <div class="pt-6 pb-2 px-4" x-show="sidebarOpen">
            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Sistema</span>
        </div>

        <x-sidebar-link :href="route('backups.index')" :active="request()->routeIs('backups.index')" icon="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" label="Respaldos DB" />
    </nav>

    <!-- Footer / User Card -->
    <div class="p-4 border-t border-gray-100 dark:border-white/5">
        <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-4 flex items-center" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
            <div class="flex items-center space-x-3" x-show="sidebarOpen">
                <div class="w-8 h-8 rounded-full bg-brand-primary flex items-center justify-center text-[10px] font-black text-white">W</div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">v4.0 Boss</p>
                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Edition</p>
                </div>
            </div>
            <button wire:click="logout" class="p-2 text-gray-400 hover:text-rose-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            </button>
        </div>
    </div>
</aside>
