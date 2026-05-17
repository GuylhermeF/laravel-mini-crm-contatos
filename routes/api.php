<?php

use Infrastructure\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index']);
    Route::post('/', [ContactController::class, 'store']);
    Route::get('/{id}', [ContactController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/{id}', [ContactController::class, 'destroy'])->where('id', '[0-9]+');
    Route::post('/{id}/process-score', [ContactController::class, 'processScore'])->where('id', '[0-9]+');
});
