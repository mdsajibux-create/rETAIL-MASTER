<?php

use App\Http\Controllers\Api\V1\Deliveryman\DeliverymanDashboardController;
use App\Http\Controllers\Api\V1\Deliveryman\DeliverymanManageController;
use App\Http\Controllers\Api\V1\Deliveryman\DeliverymanOrderManageController;
use App\Http\Controllers\Api\V1\Deliveryman\DeliverymanReviewController;
use App\Http\Controllers\Api\V1\NotificationManageController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Api\V1', 'prefix' => 'delivery-man/'], function () {
    Route::post('registration', [DeliverymanManageController::class, 'registration']);
    Route::post('login', [DeliverymanManageController::class, 'login']);

    Route::group(['middleware' => ['auth:sanctum', ApiAuthMiddleware::class], 'no.code.input'], function () {
        Route::get('dashboard', [DeliverymanDashboardController::class, 'dashboard']);
        Route::get('review', [DeliverymanReviewController::class, 'index']);
        // Notifications manage
        Route::group(['prefix' => 'notifications'], function () {
            Route::get('/', [NotificationManageController::class, 'index']);
            Route::post('/read', [NotificationManageController::class, 'markAsRead']);
        });
        Route::group(['prefix' => 'profile/'], function () {
            Route::post('activity-notification', [DeliverymanManageController::class, 'activityNotificationToggle']);
            Route::post('activate-deactivate', [DeliverymanManageController::class, 'activeDeactiveAccount']);
            Route::post('delete-account', [DeliverymanManageController::class, 'deleteAccount']);
            Route::post('change-password', [DeliverymanManageController::class, 'changePassword']);
            Route::post('change-email', [DeliverymanManageController::class, 'updateEmail']);
            Route::post('send-verification-email', [DeliverymanManageController::class, 'sendVerificationEmail']);

        });
        Route::group(['prefix' => 'order/'], function () {
            Route::get('my-orders/{order_id?}', [DeliverymanOrderManageController::class, 'getMyOrders']);
            Route::get('request', [DeliverymanOrderManageController::class, 'getOrderRequest']);
            Route::post('handle-request', [DeliverymanOrderManageController::class, 'handleOrderRequest']);
            Route::post('change-status', [DeliverymanOrderManageController::class, 'changeStatus']);
            Route::get('delivery-history', [DeliverymanOrderManageController::class, 'orderDeliveryHistory']);
        });
    });

});
