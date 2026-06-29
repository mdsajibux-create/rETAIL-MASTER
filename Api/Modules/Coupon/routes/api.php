<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Coupon\app\Http\Controllers\Api\V1\CouponManageController;

/*--------------------- coupon management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    // Coupon manage
    Route::group(['prefix' => 'coupon/', 'middleware' => ['permission:' . PermissionKey::ADMIN_COUPON_MANAGE->value]], function () {
        Route::get('list', [CouponManageController::class, 'listCoupons']);
        Route::get('coupon-wise-line', [CouponManageController::class, 'couponWiseLine']);
        Route::get('details/{id}', [CouponManageController::class, 'getCouponById']);
        Route::post('add', [CouponManageController::class, 'createCoupon']);
        Route::post('update', [CouponManageController::class, 'updateCoupon']);
        Route::patch('status-change', [CouponManageController::class, 'changeCouponStatus']);
        Route::delete('remove/{id}', [CouponManageController::class, 'deleteCoupon']);
    });

    Route::group(['prefix' => 'coupon-line/', 'middleware' => ['permission:' . PermissionKey::ADMIN_COUPON_LINE_MANAGE->value]], function () {
        Route::get('list', [CouponManageController::class, 'listCouponLines']);
        Route::get('details/{id}', [CouponManageController::class, 'getCouponLineById']);
        Route::post('add', [CouponManageController::class, 'createCouponLine']);
        Route::post('update', [CouponManageController::class, 'updateCouponLine']);
        Route::delete('remove/{id}', [CouponManageController::class, 'deleteCouponLine']);
    });

});