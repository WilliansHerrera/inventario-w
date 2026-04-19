<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\POSController;

// Alias para evitar el error 'Route [login] not defined'
Route::get('/login-alias', fn() => redirect('/login'))->name('login');

Route::get('/pos-download', function() {
    $path = public_path('downloads/POS-Scanner-Setup.exe');
    if (file_exists($path)) {
        return response()->download($path, 'POS-Scanner-Setup.exe');
    }
    return back()->with('toast', ['type' => 'error', 'message' => 'El instalador no se encuentra en el servidor.']);
})->middleware(['web', 'auth:moonshine'])->name('admin.pos.download');

// Empezar de Cero (Factory Reset) con protección de Password
Route::post('/system/factory-reset', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'password' => 'required'
    ]);

    if (!\Illuminate\Support\Facades\Hash::check($request->password, auth()->user()->password)) {
        return back()->with('toast', ['type' => 'error', 'message' => 'Contraseña incorrecta.']);
    }

    try {
        // Ejecutar reset SELECTIVO (Solo datos operativos, mantener configuracion)
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        \Illuminate\Support\Facades\DB::table('venta_detalles')->truncate();
        \Illuminate\Support\Facades\DB::table('ventas')->truncate();
        \Illuminate\Support\Facades\DB::table('inventario_movimientos')->truncate();
        \Illuminate\Support\Facades\DB::table('inventarios')->truncate();
        \Illuminate\Support\Facades\DB::table('caja_movimientos')->truncate();
        \Illuminate\Support\Facades\DB::table('cajas')->truncate();
        \Illuminate\Support\Facades\DB::table('productos')->truncate();
        \Illuminate\Support\Facades\DB::table('locales')->truncate();
        
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');

        return back()->with('toast', ['type' => 'success', 'message' => 'Datos operativos eliminados. El sistema está limpio.']);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return back()->with('toast', ['type' => 'error', 'message' => 'Error al resetear: ' . $e->getMessage()]);
    }
})->middleware(['web', 'auth:moonshine'])->name('admin.system.factory-reset');

// La ruta raíz ahora es manejada por MoonShine
// Route::get('/', fn () => view('welcome'));

Route::prefix('pos')
    ->middleware(['web', 'auth:moonshine'])
    ->group(function () {
        Route::get('/search',      [POSController::class, 'search'])->name('admin.pos.search');
        Route::post('/store',      [POSController::class, 'store'])->name('admin.pos.store');
        Route::get('/ticket/{id}', [POSController::class, 'ticket'])->name('admin.pos.ticket');
    });


// Rutas de Backup
Route::prefix('backups')
    ->middleware(['web', 'auth:moonshine'])
    ->group(function () {
        Route::post('/create', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('admin.backups.create');
        Route::post('/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('admin.backups.restore');
        Route::post('/delete', [\App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('admin.backups.delete');
        Route::get('/download/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('admin.backups.download');
    });

// Regeneración de Token de Sucursal (Locale)
Route::post('/locale/regenerate/{locale}', [\App\Http\Controllers\Admin\LocaleConfigController::class, 'regenerate'])
    ->middleware(['web', 'auth:moonshine'])
    ->name('admin.locale.regenerate-token');

// Descarga de Configuración de Sucursal (Locale)
Route::get('/locale/download/{locale}', [\App\Http\Controllers\Admin\LocaleConfigController::class, 'download'])
    ->middleware(['web', 'auth:moonshine'])
    ->name('admin.locale.download-config');
// Registro de Actualización POS con GitHub
Route::get('/pos/sync-github', function (\App\Services\GitHubUpdateService $service) {
    if (app()->environment('production')) {
        // En producción podrías querer restringir esto más, pero por ahora seguimos el plan
    }
    $result = $service->syncPosFromGithub();
    if ($result['success']) {
        return back()->with('toast', ['type' => 'success', 'message' => 'Sincronización POS Completada: ' . $result['count'] . ' versiones añadidas.']);
    }
    return back()->with('toast', ['type' => 'error', 'message' => 'Error POS: ' . $result['error']]);
})->middleware(['web', 'auth:moonshine'])->name('admin.pos.sync-github');

// Verificación de Actualizaciones Web
Route::get('/web/check-updates', function () {
    \Illuminate\Support\Facades\Cache::forget('web_latest_commits');
    return back()->with('toast', ['type' => 'success', 'message' => 'Información de GitHub actualizada.']);
})->middleware(['web', 'auth:moonshine'])->name('admin.web.check-updates');

// Ejecución de Actualización Web (PROCESO CRÍTICO CON MANEJO DE STASH)
Route::get('/web/update-now', function () {
    try {
        // Determinar ejecutable de Git (soporte para portable en subfolder o sistema)
        $gitPath = base_path('..\server\git\cmd\git.exe');
        $git = file_exists($gitPath) ? $gitPath : 'git';

        $output = [];
        
        // 1. Guardar cambios locales (Stash)
        $stashProcess = \Illuminate\Support\Facades\Process::run("$git stash");
        $stashLog = $stashProcess->output();
        
        // 2. Ejecutar Pull
        $pullProcess = \Illuminate\Support\Facades\Process::run("$git pull origin main");
        $pullLog = $pullProcess->output();
        
        if (!$pullProcess->successful()) {
            throw new \Exception("Error en Git Pull: \n" . $pullLog);
        }

        // 3. Restaurar cambios locales (Stash Pop)
        $popProcess = \Illuminate\Support\Facades\Process::run("$git stash pop");
        $popLog = $popProcess->output();

        // 4. Migraciones
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateLog = \Illuminate\Support\Facades\Artisan::output();
        
        // 5. Optimizar
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        
        \Illuminate\Support\Facades\Log::info("Web Update Executed: \nStash: $stashLog \nPull: $pullLog \nPop: $popLog \nMigrate: $migrateLog");
        
        $message = "Actualización Web Exitosa.";
        if (str_contains($popLog, 'Conflict')) {
            $message .= " NOTA: Hubo conflictos al restaurar tus cambios locales. Revisa los archivos.";
        }

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Web Update Failed: " . $e->getMessage());
        return back()->with('toast', ['type' => 'error', 'message' => 'Fallo en la actualización: ' . $e->getMessage()]);
    }
})->middleware(['web', 'auth:moonshine'])->name('admin.web.update-now');


// Impresión de Código de Barras
Route::get('/products/barcode/{producto?}', [\App\Http\Controllers\Admin\BarcodeController::class, 'print'])
    ->middleware(['web', 'auth:moonshine'])
    ->name('admin.products.barcode');

// Acciones de Caja (Turnos y Auditoría)
Route::prefix('cajas')
    ->middleware(['web', 'auth:moonshine'])
    ->group(function () {
        Route::match(['get', 'post'], '/{caja}/abrir', [\App\Http\Controllers\Admin\CajaActionController::class, 'abrirTurno'])->name('admin.caja.abrir');
        Route::match(['get', 'post'], '/{caja}/cerrar', [\App\Http\Controllers\Admin\CajaActionController::class, 'cerrarTurno'])->name('admin.caja.cerrar');
        Route::match(['get', 'post'], '/{caja}/egreso', [\App\Http\Controllers\Admin\CajaActionController::class, 'registrarEgreso'])->name('admin.caja.egreso');
        Route::get('/{caja}/summary', [\App\Http\Controllers\Admin\CajaActionController::class, 'getSummary'])->name('admin.caja.summary');
        Route::match(['get', 'post'], '/iniciar-dia', [\App\Http\Controllers\Admin\CajaActionController::class, 'iniciarDiaCompleto'])->name('admin.caja.iniciar-dia');
    });
