<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;

Route::prefix('v1')->group(function () {
    Route::middleware([\App\Http\Middleware\VerifySyncSignature::class])->group(function () {
        Route::get('/sync/products', [SyncController::class, 'products']);
        Route::post('/sync/sales', [SyncController::class, 'sales']);
        Route::get('/sync/shift/status', [SyncController::class, 'shiftStatus']);
        Route::post('/sync/shift/open', [SyncController::class, 'shiftOpen']);
        Route::post('/sync/shift/close', [SyncController::class, 'shiftClose']);
        Route::get('/sync/template', [SyncController::class, 'template']);
    });

    Route::get('/update/check', [SyncController::class, 'checkUpdate']);
});
