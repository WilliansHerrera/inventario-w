<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
        darkMode: localStorage.getItem('darkMode') === 'true',
        themeColor: localStorage.getItem('themeColor') || 'theme-indigo',
        sidebarOpen: true
      }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val)); $watch('themeColor', val => localStorage.setItem('themeColor', val))"
      :class="{ 'dark': darkMode, [themeColor]: true }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inventario-W') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-[#050505] text-gray-900 dark:text-gray-100 selection:bg-indigo-500 selection:text-white">
        
        <div class="flex min-h-screen relative overflow-hidden">
            <!-- Sidebar -->
            <livewire:layout.navigation />

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 transition-all duration-300" :class="sidebarOpen ? 'lg:ml-72' : 'lg:ml-20'">
                
                <!-- Top Header / Bar -->
                <header class="sticky top-0 z-40 bg-white/80 dark:bg-black/50 backdrop-blur-xl border-b border-gray-100 dark:border-white/5 px-4 sm:px-8 h-20 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 text-gray-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                        </button>
                        
                        @if (isset($header))
                            <div class="font-bold text-xl tracking-tight hidden sm:block">
                                {{ $header }}
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center space-x-6">
                        <!-- Theme Color Switcher -->
                        <div class="flex items-center space-x-2 bg-gray-100 dark:bg-white/5 p-1.5 rounded-full border border-gray-200 dark:border-white/10">
                            <button @click="themeColor = 'theme-indigo'" class="w-5 h-5 rounded-full bg-indigo-500 ring-2 ring-transparent" :class="themeColor === 'theme-indigo' && 'ring-indigo-500/50'"></button>
                            <button @click="themeColor = 'theme-emerald'" class="w-5 h-5 rounded-full bg-emerald-500 ring-2 ring-transparent" :class="themeColor === 'theme-emerald' && 'ring-emerald-500/50'"></button>
                            <button @click="themeColor = 'theme-rose'" class="w-5 h-5 rounded-full bg-rose-500 ring-2 ring-transparent" :class="themeColor === 'theme-rose' && 'ring-rose-500/50'"></button>
                            <button @click="themeColor = 'theme-amber'" class="w-5 h-5 rounded-full bg-amber-500 ring-2 ring-transparent" :class="themeColor === 'theme-amber' && 'ring-amber-500/50'"></button>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <button @click="darkMode = !darkMode" class="p-2.5 rounded-2xl bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:text-indigo-500 transition-all">
                            <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                            <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        </button>

                        <!-- User Profile Dropdown Placeholder (Reusing Breeze logic) -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-3 p-1.5 pr-4 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white font-black text-xs">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div class="text-left hidden lg:block">
                                    <p class="text-sm font-bold leading-none">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Administrador</p>
                                </div>
                            </button>
                            <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-3 w-48 bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-100 dark:border-white/5 p-2 z-50">
                                <a href="{{ route('profile') }}" class="block px-4 py-3 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 text-sm font-bold">Mi Perfil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-3 rounded-2xl hover:bg-rose-50 dark:hover:bg-rose-500/10 text-sm font-bold text-rose-500">Cerrar Sesión</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-4 sm:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Global Toast Notification -->
        <div 
            x-data="{ 
                messages: [],
                remove(message) {
                    this.messages = this.messages.filter(m => m !== message)
                }
            }"
            @notify.window="
                let msg = { id: Date.now(), text: $event.detail.message, type: $event.detail.type || 'info' };
                messages.push(msg);
                setTimeout(() => remove(msg), 4000);
            "
            class="fixed bottom-10 right-10 z-[100] flex flex-col space-y-4 items-end pointer-events-none"
        >
            <template x-for="msg in messages" :key="msg.id">
                <div 
                    x-show="true"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-12"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0 scale-90"
                    :class="{
                        'bg-emerald-500 shadow-emerald-500/20': msg.type === 'success',
                        'bg-rose-500 shadow-rose-500/20': msg.type === 'error',
                        'bg-indigo-600 shadow-indigo-500/20': msg.type === 'info'
                    }"
                    class="px-6 py-4 rounded-3xl text-white font-black text-xs uppercase tracking-widest shadow-2xl flex items-center space-x-3 pointer-events-auto"
                >
                    <span x-text="msg.text"></span>
                    <button @click="remove(msg)" class="ml-2 hover:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </template>
        </div>
    </body>
</html>
