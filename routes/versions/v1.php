<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require_once __DIR__ . '/../v1/modules/dashboard.php';
    require_once __DIR__ . '/../v1/modules/banks.php';
    require_once __DIR__ . '/../v1/modules/bank-accounts.php';
    require_once __DIR__ . '/../v1/modules/others-balances.php';
    require_once __DIR__ . '/../v1/modules/account-balances.php';
    require_once __DIR__ . '/../v1/modules/projects.php';
    require_once __DIR__ . '/../v1/modules/project-entries.php';
    require_once __DIR__ . '/../v1/modules/credits.php';
    require_once __DIR__ . '/../v1/modules/credit-entries.php';
    require_once __DIR__ . '/../v1/modules/prets.php';
    require_once __DIR__ . '/../v1/modules/pret-entries.php';
});
