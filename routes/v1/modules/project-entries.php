<?php

use App\Http\Controllers\ProjectEntryController;
use Illuminate\Support\Facades\Route;

Route::prefix('project-entries')->group(function () {
    Route::post('/store', [ProjectEntryController::class, 'store']);
    Route::delete('/delete', [ProjectEntryController::class, 'destroy']);
});
