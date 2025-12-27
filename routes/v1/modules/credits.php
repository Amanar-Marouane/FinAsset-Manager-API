<?php

use App\Http\Controllers\CreditController;
use Illuminate\Support\Facades\Route;

Route::prefix('credits')->group(function () {
    Route::get('/', [CreditController::class, 'index']);
    Route::get('/all', [CreditController::class, 'all']);
    Route::get('/create', [CreditController::class, 'create']);
    Route::post('/store', [CreditController::class, 'store']);
    Route::get('/edit/{id}', [CreditController::class, 'edit']);
    Route::put('/update/{id}', [CreditController::class, 'update']);
    Route::delete('/delete/{id}', [CreditController::class, 'destroy']);
});
