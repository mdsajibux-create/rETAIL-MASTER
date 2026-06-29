<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;
use Modules\Customer\app\Http\Controllers\Api\V1\CustomerManageController as AdminCustomerManageController;
use Modules\Customer\app\Http\Controllers\Api\V1\SubscriberManageController;


/*--------------------- Customer management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
        // Customer Manage
        Route::group(['prefix' => 'customer/'], function () {
            Route::group(['permission:' . PermissionKey::ADMIN_CUSTOMER_MANAGEMENT_LIST->value], function () {
                Route::get('list', [AdminCustomerManageController::class, 'listCustomers']);
                Route::get('details/{id}', [AdminCustomerManageController::class, 'getCustomerById']);
                Route::post('register', [AdminCustomerManageController::class, 'registerCustomer']);
                Route::patch('change-status', [AdminCustomerManageController::class, 'changeCustomerStatus']);
                Route::patch('change-password', [AdminCustomerManageController::class, 'changeCustomerPassword']);
                Route::patch('email-verify', [AdminCustomerManageController::class, 'verifyCustomerEmail']);
                Route::post('update-profile', [AdminCustomerManageController::class, 'updateCustomerProfile']);
                Route::patch('suspend', [AdminCustomerManageController::class, 'suspendCustomer']);
                Route::post('remove', [AdminCustomerManageController::class, 'deleteCustomer']);
                Route::get('trash-list', [AdminCustomerManageController::class, 'getCustomerTrashList'])->middleware('permission:' . PermissionKey::ADMIN_CUSTOMER_TRASH_MANAGEMENT->value);
                Route::post('trash-restore', [AdminCustomerManageController::class, 'restoreCustomerTrashed'])->middleware('permission:' . PermissionKey::ADMIN_CUSTOMER_TRASH_MANAGEMENT->value);
                Route::post('trash-delete', [AdminCustomerManageController::class, 'deleteCustomerTrashed'])->middleware('permission:' . PermissionKey::ADMIN_CUSTOMER_TRASH_MANAGEMENT->value);
            });

            // Newsletter
            Route::group(['permission:' . PermissionKey::ADMIN_CUSTOMER_MANAGEMENT_LIST->value], function () {
                Route::group(['prefix' => 'newsletter/'], function () {
                    Route::get('list', [SubscriberManageController::class, 'listSubscribers']);
                    Route::patch('bulk-status-change', [SubscriberManageController::class, 'bulkSubscriberStatusChange']);
                    Route::post('bulk-email-send', [SubscriberManageController::class, 'sendBulkEmail']);
                    Route::delete('remove/{id}', [SubscriberManageController::class, 'deleteSubscriber']);
                });
            });

        });

    // User Management
    Route::group(['middleware' => [getPermissionMiddleware('ban-user')]], function () {
        Route::patch('users/block-user', [UserController::class, 'banUser']);
        Route::patch('users/unblock-user', [UserController::class, 'activeUser']);
    });
});