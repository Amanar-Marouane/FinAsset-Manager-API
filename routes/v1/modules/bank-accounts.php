<?php

use App\Http\Controllers\BankAccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('bank-accounts')->group(function () {
    Route::get('/', [BankAccountController::class, 'index']);
    Route::get('/all', [BankAccountController::class, 'all']);
    Route::get('/year/{year}', [BankAccountController::class, 'yearlySummary']);
    Route::get('/create', [BankAccountController::class, 'create']);
    Route::post('/store', [BankAccountController::class, 'store']);
    Route::get('/edit/{id}', [BankAccountController::class, 'edit']);
    Route::put('/update/{id}', [BankAccountController::class, 'update']);
    Route::delete('/delete/{id}', [BankAccountController::class, 'destroy']);
});
