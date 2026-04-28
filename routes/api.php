<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Farmers Market Platform
|--------------------------------------------------------------------------
*/

// ── Public ──────────────────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);

// ── Authenticated ────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',     [AuthController::class, 'me']);

    // ── Admin only ───────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Supervisor management
        Route::apiResource('users/supervisors', UserController::class)
             ->names('supervisors');
        // Settings
        Route::get('/settings',        [SettingController::class, 'index']);
        Route::put('/settings/{key}',  [SettingController::class, 'update']);
    });

    // ── Admin + Supervisor ───────────────────────────────────────────
    Route::middleware('role:admin,supervisor')->group(function () {
        // Operator management (supervisor manages their own operators)
        Route::apiResource('users/operators', UserController::class)
             ->names('operators');

        // Products (write)
        Route::apiResource('products', ProductController::class)
             ->except(['index', 'show']);

        // Categories (write)
        Route::apiResource('categories', CategoryController::class)
             ->except(['index', 'show']);
    });

    // ── All authenticated roles (admin, supervisor, operator) ────────
    Route::middleware('role:admin,supervisor,operator')->group(function () {

        // Farmers
        Route::get('/farmers/search',   [FarmerController::class, 'search']);
        Route::apiResource('farmers', FarmerController::class);

        // Product catalog (lecture pour tous, dont opérateur)
        Route::get('/products',              [ProductController::class, 'index']);
        Route::get('/products/{product}',    [ProductController::class, 'show']);
        Route::get('/categories/tree',       [CategoryController::class, 'tree']);  // ← déplacé ici
        Route::get('/categories',            [CategoryController::class, 'index']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

        // Transactions
        Route::apiResource('transactions', TransactionController::class)
             ->only(['index', 'store', 'show']);

        // Debts
        Route::get('/farmers/{farmer}/debts',  [DebtController::class, 'index']);
        Route::get('/debts/{debt}',            [DebtController::class, 'show']);

        // Repayments
        Route::post('/repayments',                     [RepaymentController::class, 'store']);
        Route::get('/farmers/{farmer}/repayments',     [RepaymentController::class, 'index']);
        Route::get('/repayments/{repayment}',          [RepaymentController::class, 'show']);
    });
});
