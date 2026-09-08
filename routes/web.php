<?php

use FlussiH\Validator\Http\Controllers\FlussiHController;
use Illuminate\Support\Facades\Route;

Route::middleware(config('flussi-h.web.middleware', ['web']))
    ->prefix(trim((string) config('flussi-h.web.path', 'flussi-h'), '/'))
    ->name('flussi-h.')
    ->group(function (): void {
        Route::get('/', [FlussiHController::class, 'index'])->name('index');
        Route::post('/validate', [FlussiHController::class, 'validateFiles'])->name('validate');
    });
