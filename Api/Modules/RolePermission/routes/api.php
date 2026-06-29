<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\RolePermission\app\Http\Controllers\Api\V1\PermissionController;
use Modules\RolePermission\app\Http\Controllers\Api\V1\RoleController;
use Modules\RolePermission\app\Http\Controllers\Api\V1\StaffController;

/*--------------------- Roles &  permissions manage ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    // permission
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('permissions-for-store-owner', [PermissionController::class, 'permissionForStoreOwner']);

    // role
    Route::group(['prefix' => 'roles/', 'middleware' => 'permission:' . PermissionKey::ADMIN_USERS_ROLE_ADD->value], function () {
        Route::get('list', [RoleController::class, 'index'])->middleware('permission:' . PermissionKey::ADMIN_USERS_ROLE_LIST->value);
        Route::post('add', [RoleController::class, 'store']);
        Route::get('details/{id}', [RoleController::class, 'show']);
        Route::post('update', [RoleController::class, 'update']);
        Route::post('change-status', [RoleController::class, 'changeStatus']);
        Route::delete('remove/{id}', [RoleController::class, 'destroy']);
    });
    // Staff manage
    Route::group(['prefix' => 'staff/'], function () {
        Route::get('list', [StaffController::class, 'listStaffs'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_LIST->value]);
        Route::post('add', [StaffController::class, 'createStaff'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
        Route::get('details/{id}', [StaffController::class, 'getStaffById'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
        Route::post('update', [StaffController::class, 'updateStaff'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
        Route::patch('change-status', [StaffController::class, 'changeStaffStatus'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
        Route::patch('change-password', [StaffController::class, 'changeStaffPassword'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
        Route::post('remove', [StaffController::class, 'deleteStaffs'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
    });

});