<?php

use App\Http\Controllers\PretEntryController;
use Illuminate\Support\Facades\Route;

Route::prefix('pret-entries')->group(function () {
    Route::get('/', [PretEntryController::class, 'index']);
    Route::get('/all', [PretEntryController::class, 'all']);
    Route::get('/create', [PretEntryController::class, 'create']);
    Route::post('/store', [PretEntryController::class, 'store']);
    Route::get('/edit/{id}', [PretEntryController::class, 'edit']);
    Route::put('/update/{id}', [PretEntryController::class, 'update']);
    Route::delete('/delete/{id}', [PretEntryController::class, 'destroy']);
});
