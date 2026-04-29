<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">{{ __('Copias de Seguridad') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Gestiona los respaldos de la base de datos') }} `inventario_w`</p>
        </div>
        <form action="{{ route('admin.backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('Generar Nueva Copia') }}
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500">{{ __('Archivo') }}</th>
                    <th class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500">{{ __('Fecha') }}</th>
                    <th class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500 text-center">{{ __('Tamaño') }}</th>
                    <th class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500 text-right">{{ __('Acciones') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php $backups = app(\App\Services\BackupService::class)->listBackups(); @endphp
                @forelse($backups as $backup)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-slate-700">{{ $backup['filename'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500 font-medium">{{ $backup['date'] }}</td>
                        <td class="px-6 py-4 text-sm text-slate-400 font-bold text-center">{{ $backup['size'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Download --}}
                                <a href="{{ route('admin.backups.download', $backup['filename']) }}" 
                                   class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="{{ __('Descargar') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>

                                {{-- Restore (with confirmation) --}}
                                <form action="{{ route('admin.backups.restore') }}" method="POST" 
                                      onsubmit="return confirm('{{ __('¿ESTÁS SEGURO? Esta acción sobrescribirá TODA la base de datos actual con la información de este backup. Los datos actuales se perderán.') }}')">
                                    @csrf
                                    <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="{{ __('Restaurar a este punto') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('admin.backups.delete') }}" method="POST" 
                                      onsubmit="return confirm('{{ __('¿Eliminar esta copia de seguridad?') }}')">
                                    @csrf
                                    <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="{{ __('Eliminar') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-500">{{ __('No hay copias de seguridad disponibles.') }}</p>
                                <p class="text-xs text-slate-400">{{ __('Genera una copia para proteger tus datos.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl flex gap-3 items-start">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="text-xs text-amber-800 leading-relaxed">
            <p class="font-black uppercase tracking-wider mb-1">{{ __('¡Aviso Importante!') }}</p>
            <p>{{ __('Las restauraciones son irreversibles. Siempre descarga una copia del estado actual antes de restaurar una copia antigua para evitar pérdida accidental de información.') }}</p>
        </div>
    </div>
</div>
