<?php

use App\Http\Controllers\BuildingController;
use Illuminate\Support\Facades\Route;

Route::prefix('buildings')->group(function () {
    Route::get('/', [BuildingController::class, 'index']);
    Route::get('/create', [BuildingController::class, 'create']);
    Route::post('/store', [BuildingController::class, 'store']);
    Route::get('/edit/{id}', [BuildingController::class, 'edit']);
    Route::put('/update/{id}', [BuildingController::class, 'update']);
    Route::delete('/delete/{id}', [BuildingController::class, 'destroy']);
});
