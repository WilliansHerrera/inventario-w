<?php

use Livewire\Volt\Component;
use App\Services\BackupService;

new class extends Component {
    public function createBackup(BackupService $backupService)
    {
        $result = $backupService->createBackup();
        
        if ($result) {
            $this->dispatch('notify', ['message' => 'Respaldo creado: ' . $result, 'type' => 'success']);
        } else {
            $this->dispatch('notify', ['message' => 'Error al crear respaldo', 'type' => 'error']);
        }
    }

    public function with(BackupService $backupService)
    {
        return [
            'backups' => $backupService->getBackups(),
        ];
    }
}; ?>

<div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-xl border border-gray-100 dark:border-gray-800">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter">Respaldos de Seguridad</h3>
            <p class="text-sm text-gray-500">Gestione copias de seguridad de su base de datos local.</p>
        </div>
        <button wire:click="createBackup" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 shadow-xl shadow-indigo-500/20 transition-all">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
            Crear Respaldo Ahora
        </button>
    </div>

    <div class="space-y-4">
        @forelse($backups as $backup)
        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-white dark:bg-gray-900 rounded-xl flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <div class="font-bold text-gray-900 dark:text-gray-100">{{ $backup }}</div>
                    <div class="text-xs text-gray-500 font-mono">Ubicación: storage/app/backups/</div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Listo</span>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-gray-400 opacity-50">
            <p>No hay respaldos creados todavía.</p>
        </div>
        @endforelse
    </div>
</div>
