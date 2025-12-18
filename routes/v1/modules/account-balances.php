<?php

use App\Http\Controllers\AccountBalanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('account-balances')->group(function () {
    Route::get('/', [AccountBalanceController::class, 'index']);
    Route::post('/store', [AccountBalanceController::class, 'store']);
    Route::get('/edit/{id}', [AccountBalanceController::class, 'edit']);
    Route::put('/update/{id}', [AccountBalanceController::class, 'update']);
    Route::delete('/delete/{id}', [AccountBalanceController::class, 'destroy']);
});
