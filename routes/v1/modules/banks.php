<?php

use App\Http\Controllers\BankController;
use Illuminate\Support\Facades\Route;

Route::prefix('banks')->group(function () {
    Route::get('/', [BankController::class, 'index']);
    Route::get('/all', [BankController::class, 'all']);
    Route::get('/create', [BankController::class, 'create']);
    Route::post('/store', [BankController::class, 'store']);
    Route::get('/edit/{id}', [BankController::class, 'edit']);
    Route::put('/update/{id}', [BankController::class, 'update']);
    Route::delete('/delete/{id}', [BankController::class, 'destroy']);
});
