<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);
    Route::get('/create', [ProjectController::class, 'create']);
    Route::post('/store', [ProjectController::class, 'store']);
    Route::get('/edit/{id}', [ProjectController::class, 'edit']);
    Route::put('/update/{id}', [ProjectController::class, 'update']);
    Route::delete('/delete/{id}', [ProjectController::class, 'destroy']);
    Route::get('/by-year', [ProjectController::class, 'byYear']);
});
