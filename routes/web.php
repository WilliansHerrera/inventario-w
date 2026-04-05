<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\POSController;

Route::get('/admin/pos-download', function() {
    $path = storage_path('app/public/pos/POS-Setup.exe');
    if (file_exists($path)) {
        return response()->download($path);
    }
    return "El instalador se está generando o no se encuentra en `storage/app/public/pos/POS-Setup.exe`.<br><br><b>Instrucciones del Instalador Único:</b><br>1. Descarga la carpeta `POS-Windows`.<br>2. Ejecuta `build.ps1` en tu PC local (él hará todo el trabajo).<br>3. Sube el `.exe` generado a la ruta indicada en el servidor.";
})->middleware(['web', 'auth:moonshine'])->name('admin.pos.download');

Route::get('/', fn () => view('welcome'));

Route::prefix('admin/pos')
    ->middleware(['web', 'auth:moonshine'])
    ->group(function () {
        Route::get('/search',      [POSController::class, 'search'])->name('admin.pos.search');
        Route::post('/store',      [POSController::class, 'store'])->name('admin.pos.store');
        Route::get('/ticket/{id}', [POSController::class, 'ticket'])->name('admin.pos.ticket');
    });


// Rutas de Backup
Route::prefix('admin/backups')
    ->middleware(['web', 'auth:moonshine'])
    ->group(function () {
        Route::post('/create', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('admin.backups.create');
        Route::post('/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('admin.backups.restore');
        Route::post('/delete', [\App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('admin.backups.delete');
        Route::get('/download/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('admin.backups.download');
    });
