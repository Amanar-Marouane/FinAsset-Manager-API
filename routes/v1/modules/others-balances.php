<?php

use App\Http\Controllers\OthersBalanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('others-balances')->group(function () {
    Route::get('/', [OthersBalanceController::class, 'index']);
    Route::post('/store', [OthersBalanceController::class, 'store']);
    Route::get('/year/{year}', [OthersBalanceController::class, 'yearlySummary']);
});
