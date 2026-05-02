<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight flex items-center">
                <svg class="w-8 h-8 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                Terminal de Ventas
            </h2>
            <div class="flex items-center space-x-4">
                <span class="text-xs font-black text-gray-400 uppercase tracking-widest">Estado:</span>
                <div class="flex items-center bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-2"></div>
                    <span class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest">En Línea</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            <livewire:pos-terminal />
        </div>
    </div>
</x-app-layout>
