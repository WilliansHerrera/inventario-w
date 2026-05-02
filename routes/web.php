<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CajaActionController;
use App\Http\Controllers\Admin\LocaleConfigController;
use App\Http\Controllers\Admin\POSController;
use App\Http\Controllers\Admin\SystemUpdateController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Alias para evitar el error 'Route [login] not defined'
Route::get('/login-alias', fn () => redirect('/login'))->name('login');

// Rutas de Branding
Route::get('/branding/logo.svg', [BrandingController::class, 'logo'])->name('branding.logo');
Route::get('/branding/logo-light.svg', [BrandingController::class, 'logoLight'])->name('branding.logo-light');
Route::get('/branding/logo-small.svg', [BrandingController::class, 'logoSmall'])->name('branding.logo-small');

Route::middleware(['web', 'auth:moonshine,web'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // Rutas de Administración (MoonShine Compatibility)
    Route::get('/pos-download', [POSController::class, 'download'])->name('admin.pos.download');

    Route::prefix('pos')->group(function () {
        Route::get('/search', [POSController::class, 'search'])->name('admin.pos.search');
        Route::post('/store', [POSController::class, 'store'])
            ->middleware('throttle:pos-sales')
            ->name('admin.pos.store');
        Route::get('/ticket/{id}', [POSController::class, 'ticket'])->name('admin.pos.ticket');
    });

    Route::prefix('backups')->group(function () {
        Route::post('/create', [BackupController::class, 'create'])->name('admin.backups.create');
        Route::post('/restore', [BackupController::class, 'restore'])->name('admin.backups.restore');
        Route::post('/delete', [BackupController::class, 'delete'])->name('admin.backups.delete');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('admin.backups.download');
    });

    Route::post('/locale/regenerate/{locale}', [LocaleConfigController::class, 'regenerate'])->name('admin.locale.regenerate-token');
    Route::get('/locale/download/{locale}', [LocaleConfigController::class, 'download'])->name('admin.locale.download-config');

    Route::get('/products/barcode/{producto?}', [BarcodeController::class, 'print'])->name('admin.products.barcode');

    // Rutas de Sistema y Actualizaciones
    Route::prefix('system')->group(function () {
        Route::get('/web/check', [SystemUpdateController::class, 'checkUpdates'])->name('admin.web.check-updates');
        Route::get('/web/update', [SystemUpdateController::class, 'updateNow'])->name('admin.web.update-now');
        Route::get('/pos/sync', [SystemUpdateController::class, 'syncPos'])->name('admin.pos.sync-github');
        Route::match(['get', 'post'], '/factory-reset', [SystemUpdateController::class, 'factoryReset'])->name('admin.system.factory-reset');
    });

    Route::prefix('cajas')->group(function () {
        Route::match(['get', 'post'], '/{caja}/abrir', [CajaActionController::class, 'abrirTurno'])->name('admin.caja.abrir');
        Route::match(['get', 'post'], '/{caja}/cerrar', [CajaActionController::class, 'cerrarTurno'])->name('admin.caja.cerrar');
        Route::match(['get', 'post'], '/{caja}/egreso', [CajaActionController::class, 'registrarEgreso'])->name('admin.caja.egreso');
        Route::get('/{caja}/summary', [CajaActionController::class, 'getSummary'])->name('admin.caja.summary');
        Route::match(['get', 'post'], '/iniciar-dia', [CajaActionController::class, 'iniciarDiaCompleto'])->name('admin.caja.iniciar-dia');
    });
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

require __DIR__.'/auth.php';
