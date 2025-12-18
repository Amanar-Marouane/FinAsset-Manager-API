<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require_once __DIR__ . '/../v1/modules/dashboard.php';
    require_once __DIR__ . '/../v1/modules/buildings.php';
    require_once __DIR__ . '/../v1/modules/building-types.php';
    require_once __DIR__ . '/../v1/modules/banks.php';
    require_once __DIR__ . '/../v1/modules/bank-accounts.php';
    require_once __DIR__ . '/../v1/modules/account-balances.php';
    require_once __DIR__ . '/../v1/modules/cars.php';
    require_once __DIR__ . '/../v1/modules/terrains.php';
    require_once __DIR__ . '/../v1/modules/projects.php';
    require_once __DIR__ . '/../v1/modules/credits.php';
    require_once __DIR__ . '/../v1/modules/prets.php';
});
