@php
    $backupService = app(\App\Services\BackupService::class);
    $backups = $backupService->listBackups();
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">Gestión de Copias de Seguridad</h2>
            <p class="text-sm text-slate-500">Respalda y restaura tu base de datos localmente.</p>
        </div>
        
        <form action="{{ route('admin.backups.create') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary flex items-center gap-2">
                <x-moonshine::icon icon="plus" size="4" />
                Crear Nueva Copia
            </button>
        </form>
    </div>

    <div class="table-container shadow-sm border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
        <table class="table w-full">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Archivo</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha</th>
                    <th class="text-left px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Tamaño</th>
                    <th class="text-right px-4 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse($backups as $backup)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-4 py-3 text-sm font-medium text-slate-800 dark:text-slate-200">
                            {{ $backup['filename'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500">
                            {{ $backup['date'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-500">
                            {{ $backup['size'] }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Download --}}
                                <a href="{{ route('admin.backups.download', $backup['filename']) }}" 
                                   class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors"
                                   title="Descargar">
                                    <x-moonshine::icon icon="arrow-down-tray" size="4" />
                                </a>

                                {{-- Restore --}}
                                <form action="{{ route('admin.backups.restore') }}" method="POST" 
                                      onsubmit="return confirm('¿Estás seguro de restaurar esta copia? Se sobrescribirán los datos actuales.')">
                                    @csrf
                                    <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="p-2 text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-colors"
                                            title="Restaurar">
                                        <x-moonshine::icon icon="arrow-path" size="4" />
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('admin.backups.delete') }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar definitivamente este archivo?')">
                                    @csrf
                                    <input type="hidden" name="filename" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors"
                                            title="Eliminar">
                                        <x-moonshine::icon icon="trash" size="4" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 italic">
                            No se han encontrado copias de seguridad.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900/50 rounded-xl p-4 flex gap-3">
        <x-moonshine::icon icon="information-circle" size="6" class="text-amber-500 shrink-0" />
        <div class="text-sm text-amber-800 dark:text-amber-200">
            <p class="font-bold">Advertencia de Seguridad</p>
            <p>La restauración de una copia de seguridad sobrescribirá toda la base de datos actual. Asegúrate de tener un respaldo reciente antes de proceder.</p>
        </div>
    </div>
</div>
