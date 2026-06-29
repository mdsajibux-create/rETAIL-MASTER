<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Pos\app\Http\Controllers\Api\AdminPosSaleController;
use Modules\Pos\app\Http\Controllers\Api\BranchPosSaleController;


/* --------------------- Admin route start ------------------------- */
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    Route::group(['prefix' => 'pos/', 'middleware' => 'permission:' . PermissionKey::ADMIN_POS_SALES->value], function () {
        Route::post('checkout', [AdminPosSaleController::class, 'createOrder']);
        Route::get('products', [AdminPosSaleController::class, 'listProducts']);
        Route::get('products/{slug}', [AdminPosSaleController::class, 'getProductBySlug']);
        Route::get('customers', [AdminPosSaleController::class, 'listCustomers']);
        Route::post('add-customer', [AdminPosSaleController::class, 'addCustomer']);
        Route::post('invoice', [AdminPosSaleController::class, 'invoice']);
        Route::get('orders/{order_id?}', [AdminPosSaleController::class, 'orders'])->middleware('permission:' . PermissionKey::ADMIN_POS_ORDERS->value);

        Route::group(['prefix' => '/settings', 'middleware' => ['permission:' . PermissionKey::ADMIN_POS_SETTINGS->value]], function () {
            Route::get('/', [AdminPosSaleController::class, 'posSettings']);
            Route::put('/', [AdminPosSaleController::class, 'updatePosSettings']);
        });
    });
});

/* --------------------- Branch route start ------------------------- */
Route::middleware(['auth:sanctum'])->prefix('v1/branch/')->group(function () {
    Route::group(['prefix' => 'pos/', 'middleware' => 'permission:' . PermissionKey::BRANCH_POS_SALES->value], function () {
        Route::post('checkout', [BranchPosSaleController::class, 'createOrder']);
        Route::get('products', [BranchPosSaleController::class, 'listProducts']);
        Route::get('products/{slug}', [BranchPosSaleController::class, 'getProductBySlug']);
        Route::get('customers', [BranchPosSaleController::class, 'listCustomers']);
        Route::post('add-customer', [BranchPosSaleController::class, 'addCustomer']);
        Route::post('invoice', [BranchPosSaleController::class, 'invoice']);
        Route::get('orders/{order_id?}', [BranchPosSaleController::class, 'orders'])->middleware('permission:' . PermissionKey::BRANCH_POS_ORDERS->value);
    });
});
