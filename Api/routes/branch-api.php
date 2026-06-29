<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\BranchDashboardManageController;
use App\Http\Controllers\Api\V1\BranchManageController;
use App\Http\Controllers\Api\V1\BranchOrderController;
use App\Http\Controllers\Api\V1\BranchOrderRefundManageController;
use App\Http\Controllers\Api\V1\NotificationManageController;
use Illuminate\Support\Facades\Route;
use Modules\RolePermission\app\Http\Controllers\Api\V1\BranchStaffManageController;


Route::group(['namespace' => 'Api\V1'], function () {
    Route::group(['prefix' => 'branch/'], function () {
        // login routes
        Route::post('login', [LoginController::class, 'login']);
        Route::post('forget-password', [BranchManageController::class, 'forgetPassword']);
        Route::post('verify-token', [BranchManageController::class, 'verifyToken']);
        Route::patch('reset-password', [BranchManageController::class, 'resetPassword']);

       Route::group(['middleware' => ['auth:sanctum']], function () {
        // profile manage
        Route::group(['prefix' => 'profile/'], function () {
            Route::get('/', [BranchManageController::class, 'profile']);
            Route::post('/update', [BranchManageController::class, 'updateProfile']);
            Route::post('/change-email', [BranchManageController::class, 'updateEmail']);
        });

        Route::group(['prefix' => 'dashboard/'], function () {
            Route::get('/', [BranchDashboardManageController::class, 'summaryData']);
            Route::get('sales-summary', [BranchDashboardManageController::class, 'salesSummaryData']);
            Route::get('other-summary', [BranchDashboardManageController::class, 'otherSummaryData']);
            Route::get('order-growth-summary', [BranchDashboardManageController::class, 'orderGrowthData']);
        });

        // orders manage
        Route::group(['prefix' => 'orders/'], function () {
            Route::group(['middleware' => ['permission:' . PermissionKey::BRANCH_ORDER_MANAGE->value]], function () {
                Route::get('invoice', [BranchOrderController::class, 'orderInvoice']);
                Route::post('change-order-status', [BranchOrderController::class, 'changeOrderStatus']);
                Route::post('cancel-order', [BranchOrderController::class, 'cancelOrder']);
                Route::get('refund-request', [BranchOrderRefundManageController::class, 'orderRefundRequests'])->middleware('permission:' . PermissionKey::SELLER_ORDERS_RETURNED_OR_REFUND_REQUEST->value);
                Route::post('refund-request/handle', [BranchOrderRefundManageController::class, 'handleRefundRequest'])->middleware('permission:' . PermissionKey::SELLER_ORDERS_RETURNED_OR_REFUND_REQUEST->value);
                Route::get('{order_id?}', [BranchOrderController::class, 'allOrders']);
            });
        });

            // Staff manage
            Route::group(['prefix' => 'staff/', 'middleware' => ['permission:' . PermissionKey::BRANCH_STAFF_MANAGE->value]], function () {
                Route::get('list', [BranchStaffManageController::class, 'listStaffs']);
                Route::post('add', [BranchStaffManageController::class, 'createStaff']);
                Route::get('details/{id}', [BranchStaffManageController::class, 'getStaffById']);
                Route::post('update', [BranchStaffManageController::class, 'updateStaff']);
                Route::post('change-status', [BranchStaffManageController::class, 'changeStaffStatus']);
                Route::post('remove', [BranchStaffManageController::class, 'deleteStaffs']);
            });

            // Notifications manage
            Route::prefix('notifications/')->middleware(['permission:' . PermissionKey::BRANCH_NOTIFICATION_MANAGEMENT->value])->group(function () {
                Route::get('/', [NotificationManageController::class, 'listNotifications']);
                Route::post('/read', [NotificationManageController::class, 'markAsRead']);
            });

       });
    });
});
