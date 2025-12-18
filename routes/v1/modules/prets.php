<?php

use App\Http\Controllers\PretController;
use Illuminate\Support\Facades\Route;

Route::prefix('prets')->group(function () {
    Route::get('/', [PretController::class, 'index']);
    Route::get('/create', [PretController::class, 'create']);
    Route::post('/store', [PretController::class, 'store']);
    Route::get('/edit/{id}', [PretController::class, 'edit']);
    Route::put('/update/{id}', [PretController::class, 'update']);
    Route::delete('/delete/{id}', [PretController::class, 'destroy']);
});
