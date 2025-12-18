<?php

use App\Http\Controllers\BuildingTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('building-types')->group(function () {
    Route::get('/', [BuildingTypeController::class, 'index']);
    Route::get('/create', [BuildingTypeController::class, 'create']);
    Route::post('/store', [BuildingTypeController::class, 'store']);
    Route::get('/edit/{id}', [BuildingTypeController::class, 'edit']);
    Route::put('/update/{id}', [BuildingTypeController::class, 'update']);
    Route::delete('/delete/{id}', [BuildingTypeController::class, 'destroy']);
});
