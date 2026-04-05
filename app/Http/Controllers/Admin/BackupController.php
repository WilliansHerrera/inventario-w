<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use MoonShine\Laravel\MoonShineUI;
use MoonShine\Support\Enums\ToastType;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Create a new backup.
     */
    public function create()
    {
        $result = $this->backupService->createBackup();

        if ($result['success']) {
             MoonShineUI::toast('Copia de seguridad creada: ' . $result['filename'], ToastType::SUCCESS);
        } else {
             MoonShineUI::toast($result['message'], ToastType::ERROR);
        }

        return back();
    }

    /**
     * Restore a backup.
     */
    public function restore(Request $request)
    {
        $filename = $request->input('filename');
        
        if (!$filename) {
            MoonShineUI::toast('Archivo no especificado.', ToastType::ERROR);
            return back();
        }

        $result = $this->backupService->restoreBackup($filename);

        if ($result['success']) {
            MoonShineUI::toast($result['message'], ToastType::SUCCESS);
        } else {
            MoonShineUI::toast($result['message'], ToastType::ERROR);
        }

        return back();
    }

    /**
     * Delete a backup.
     */
    public function delete(Request $request)
    {
        $filename = $request->input('filename');
        
        if (!$filename) {
            MoonShineUI::toast('Archivo no especificado.', ToastType::ERROR);
            return back();
        }

        if ($this->backupService->deleteBackup($filename)) {
            MoonShineUI::toast('Copia de seguridad eliminada.', ToastType::SUCCESS);
        } else {
            MoonShineUI::toast('No se pudo eliminar el archivo.', ToastType::ERROR);
        }

        return back();
    }

    /**
     * Download a backup file.
     */
    public function download(string $filename)
    {
        $path = $this->backupService->getBackupPath($filename);

        if (!$path) {
            abort(404, 'Archivo no encontrado.');
        }

        return response()->download($path);
    }
}
