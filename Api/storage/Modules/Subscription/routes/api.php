<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Subscription\app\Http\Controllers\Api\AdminSubscriptionPackageController;
use Modules\Subscription\app\Http\Controllers\Api\AdminSubscriptionSellerController;
use Modules\Subscription\app\Http\Controllers\Api\AdminSubscriptionSettingsController;
use Modules\Subscription\app\Http\Controllers\Api\BuySubscriptionPackageController;
use Modules\Subscription\app\Http\Controllers\Api\SubscriptionPackageController;
use Modules\Subscription\app\Http\Controllers\StoreSubscriptionManageController;


//  Admin subscription package manage
Route::middleware(['auth:sanctum'])->prefix('v1/admin/business-operations/subscription/')->group(function () {
    Route::prefix('package/')->middleware(['permission:' . PermissionKey::ADMIN_SUBSCRIPTION_PACKAGE_MANAGE->value])->group(function () {
        Route::get('list', [AdminSubscriptionPackageController::class, 'index']);
        Route::post('store', [AdminSubscriptionPackageController::class, 'store']);
        Route::get('details/{id}', [AdminSubscriptionPackageController::class, 'show']);
        Route::post('update', [AdminSubscriptionPackageController::class, 'update']);
        Route::post('change-status', [AdminSubscriptionPackageController::class, 'statusChange']);
        Route::delete('delete/{id}', [AdminSubscriptionPackageController::class, 'destroy']);
    });
    // seller list
    Route::prefix('store')->middleware(['permission:' . PermissionKey::ADMIN_SUBSCRIPTION_STORE_PACKAGE_MANAGE->value])->group(function () {
        Route::get('list', [AdminSubscriptionSellerController::class, 'index']);
        Route::get('history/{id?}', [AdminSubscriptionSellerController::class, 'subscriptionHistory']);
        Route::post('assign', [AdminSubscriptionSellerController::class, 'assignStoreSubscription']);
        Route::post('change-status', [AdminSubscriptionSellerController::class, 'statusChange']);
    });
});


    /* --------------------- Seller route start ------------------------- */
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::group(['prefix' => 'v1/seller/store/subscription/package/'], function () {
           Route::get('history', [StoreSubscriptionManageController::class, 'subscriptionPackageHistory']);
        });
    });


//package lists
Route::prefix('v1/subscription/')->group(function () {
    Route::get('packages', [SubscriptionPackageController::class, 'packages']);
    // buy store subscription
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('package/buy', [BuySubscriptionPackageController::class, 'buySubscriptionPackage']);
        Route::post('package/renew', [BuySubscriptionPackageController::class, 'renewSubscriptionPackage']);
        Route::post('package/payment-status-update', [BuySubscriptionPackageController::class, 'packagePaymentStatusUpdate']);
    });
});


