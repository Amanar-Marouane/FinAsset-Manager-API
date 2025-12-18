<?php

use App\Http\Controllers\TerrainController;
use Illuminate\Support\Facades\Route;

Route::prefix('terrains')->group(function () {
    Route::get('/', [TerrainController::class, 'index']);
    Route::get('/create', [TerrainController::class, 'create']);
    Route::post('/store', [TerrainController::class, 'store']);
    Route::get('/edit/{id}', [TerrainController::class, 'edit']);
    Route::put('/update/{id}', [TerrainController::class, 'update']);
    Route::delete('/delete/{id}', [TerrainController::class, 'destroy']);
});
