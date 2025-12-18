<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::post('/is-logged', [AuthController::class, 'isLogged']);

Route::middleware('guest.only')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/sign-in', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    });
});

Route::middleware('jwt.guard')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/sign-out', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});
