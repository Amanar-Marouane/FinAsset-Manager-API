<?php

use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json(['status' => 'API is running']);
});

require __DIR__ . '/auth.php';
require_once __DIR__ . '/versions/v1.php';
