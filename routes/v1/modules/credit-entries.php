<?php

use App\Http\Controllers\CreditEntryController;
use Illuminate\Support\Facades\Route;

Route::prefix('credit-entries')->group(function () {
    Route::get('/', [CreditEntryController::class, 'index']);
    Route::get('/all', [CreditEntryController::class, 'all']);
    Route::get('/create', [CreditEntryController::class, 'create']);
    Route::post('/store', [CreditEntryController::class, 'store']);
    Route::get('/edit/{id}', [CreditEntryController::class, 'edit']);
    Route::put('/update/{id}', [CreditEntryController::class, 'update']);
    Route::delete('/delete/{id}', [CreditEntryController::class, 'destroy']);
});
