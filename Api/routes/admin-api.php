<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\Admin\AdminContactManageController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationManageController;
use App\Http\Controllers\Api\V1\OpenAiController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;
use Modules\RolePermission\app\Http\Controllers\Api\V1\PermissionController;


Route::group(['namespace' => 'Api\V1', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/logout', [UserController::class, 'logout']);
    // media manage
    Route::group(['prefix' => 'media-upload'], function () {
        Route::post('/store', [MediaController::class, 'mediaUpload']);
        Route::get('/load-more', [MediaController::class, 'load_more']);
        Route::post('/alt', [MediaController::class, 'alt_change']);
        Route::post('/delete', [MediaController::class, 'delete_media']);
    });

    /* --------------------- Admin route start ------------------------- */
    Route::group(['prefix' => 'admin/'], function () {
        // Dashboard manage
        Route::group(['prefix' => 'dashboard/', 'middleware' => ['permission:' . PermissionKey::ADMIN_DASHBOARD->value]], function () {
            Route::get('/', [AdminDashboardController::class, 'summaryData']);
            Route::get('sales-summary', [AdminDashboardController::class, 'salesSummaryData']);
            Route::get('other-summary', [AdminDashboardController::class, 'otherSummaryData']);
            Route::get('order-growth-summary', [AdminDashboardController::class, 'orderGrowthData']);
        });

        // contact message
        Route::group(['prefix' => 'contact-messages/', 'middleware' => ['permission:' . PermissionKey::ADMIN_CUSTOMER_CONTACT_MESSAGES->value]], function () {
            Route::get('list', [AdminContactManageController::class, 'listContacts']);
            Route::post('reply', [AdminContactManageController::class, 'replyContacts']);
            Route::patch('change-status', [AdminContactManageController::class, 'changeContactStatus']);
            Route::post('remove', [AdminContactManageController::class, 'deleteContacts']);
        });

        // Notifications manage
        Route::prefix('notifications/')->middleware(['permission:' . PermissionKey::ADMIN_NOTIFICATION_MANAGEMENT->value])->group(function () {
            Route::get('/', [NotificationManageController::class, 'listNotifications']);
            Route::patch('/read', [NotificationManageController::class, 'markAsRead']);
            Route::post('remove', [NotificationManageController::class, 'destroy']);
        });

        Route::get('module-wise-permissions', [PermissionController::class, 'moduleWisePermissions']);

        // open ai
        Route::post('/generate/content', [OpenAiController::class, 'generateContent']);
    });
});
