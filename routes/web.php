<?php

use FlussiH\Validator\Http\Controllers\FlussiHController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('flussi-h.web.middleware', ['web']))
    ->prefix(trim((string) config('flussi-h.web.path', 'flussi-h'), '/'))
    ->name('flussi-h.')
    ->group(function (): void {
        Route::get('/assets/software-engine-logo.webp', [FlussiHController::class, 'brandLogo'])->name('brand-logo');
        Route::get('/assets/software-engine-icon.png', [FlussiHController::class, 'brandIcon'])->name('brand-icon');
        Route::get('/', [FlussiHController::class, 'index'])->name('index');
        Route::post('/validate', [FlussiHController::class, 'validateFiles'])->name('validate');
    });
