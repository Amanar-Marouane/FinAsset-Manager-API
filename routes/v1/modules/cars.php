<?php

use App\Http\Controllers\CarController;
use Illuminate\Support\Facades\Route;

Route::prefix('cars')->group(function () {
    Route::get('/', [CarController::class, 'index']);
    Route::get('/create', [CarController::class, 'create']);
    Route::post('/store', [CarController::class, 'store']);
    Route::get('/edit/{id}', [CarController::class, 'edit']);
    Route::put('/update/{id}', [CarController::class, 'update']);
    Route::delete('/delete/{id}', [CarController::class, 'destroy']);
});
