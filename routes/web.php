<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\POSController;

Route::get('/admin/pos-download', function() {
    $path = storage_path('app/public/pos/POS-Setup.exe');
    if (file_exists($path)) {
        return response()->download($path, 'POS-Setup.exe', [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
    return "El instalador se está generando o no se encuentra...";
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

// Regeneración de Token de Sucursal (Locale)
Route::post('/admin/locale/regenerate/{locale}', [\App\Http\Controllers\Admin\LocaleConfigController::class, 'regenerate'])
    ->middleware(['web', 'auth:moonshine'])
    ->name('admin.locale.regenerate-token');

// Descarga de Configuración de Sucursal (Locale)
Route::get('/admin/locale/download/{locale}', [\App\Http\Controllers\Admin\LocaleConfigController::class, 'download'])
    ->middleware(['web', 'auth:moonshine'])
    ->name('admin.locale.download-config');
// Registro de Actualización POS con GitHub
Route::get('/admin/pos/sync-github', function (\App\Services\GitHubUpdateService $service) {
    if (app()->environment('production')) {
        // En producción podrías querer restringir esto más, pero por ahora seguimos el plan
    }
    $result = $service->syncPosFromGithub();
    if ($result['success']) {
        return back()->with('toast', 'Sincronización POS Completada: ' . $result['count'] . ' versiones añadidas.');
    }
    return back()->with('toast', 'Error POS: ' . $result['error'])->danger();
})->middleware(['web', 'auth:moonshine'])->name('admin.pos.sync-github');

// Verificación de Actualizaciones Web
Route::get('/admin/web/check-updates', function () {
    \Illuminate\Support\Facades\Cache::forget('web_latest_commits');
    return back()->with('toast', 'Información de GitHub actualizada.');
})->middleware(['web', 'auth:moonshine'])->name('admin.web.check-updates');

// Ejecución de Actualización Web (PROCESO CRÍTICO)
Route::get('/admin/web/update-now', function () {
    try {
        // 1. git pull (Asumimos que git está configurado y hay repo)
        $output = [];
        exec('git pull origin main 2>&1', $output);
        $pullLog = implode("\n", $output);
        
        // 2. artisan migrate
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateLog = \Illuminate\Support\Facades\Artisan::output();
        
        // 3. artisan optimize
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        
        Log::info("Web Update Executed: \n$pullLog \n$migrateLog");
        
        return back()->with('toast', 'Actualización Web Exitosa. Revisa los logs para detalles.');
    } catch (\Exception $e) {
        Log::error("Web Update Failed: " . $e->getMessage());
        return back()->with('toast', 'Fallo en la actualización: ' . $e->getMessage())->danger();
    }
})->middleware(['web', 'auth:moonshine'])->name('admin.web.update-now');
