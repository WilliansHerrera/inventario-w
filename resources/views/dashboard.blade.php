<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Dashboard Industrial "Boss Edition"') }}
            </h2>
            <div class="flex space-x-2">
                <span class="bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded">V5.0</span>
                <span class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold px-2 py-1 rounded">LARAVEL 12 + TALL</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-950 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Dynamic Stats Component -->
            <livewire:dashboard-stats />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Top Selling Products -->
                <div class="lg:col-span-2 space-y-8">
                    <livewire:dashboard.top-products />
                </div>

                <!-- Secondary Info (Alerts & Actions) -->
                <div class="space-y-8">
                    <!-- Alerts -->
                    <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div class="p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center text-gray-900 dark:text-gray-100">
                                <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                Alertas de Sistema
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-start p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-900/30">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-red-800 dark:text-red-200">Revisar stock en Inventario</p>
                                    </div>
                                </div>
                                <div class="flex items-start p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-900/30">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Revisar cortes de caja pendientes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Mini -->
                    <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] sm:rounded-2xl border border-gray-100 dark:border-gray-800">
                        <div class="p-6">
                            <h3 class="text-lg font-bold mb-4 flex items-center text-gray-900 dark:text-gray-100">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Operaciones Rápidas
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('pos.index') }}" class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all text-center group border border-transparent dark:hover:border-indigo-500/30">
                                    <svg class="w-6 h-6 text-indigo-500 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Ir al POS</span>
                                </a>
                                <a href="#" class="p-3 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-all text-center group border border-transparent dark:hover:border-emerald-500/30">
                                    <svg class="w-6 h-6 text-emerald-500 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Inventario</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
