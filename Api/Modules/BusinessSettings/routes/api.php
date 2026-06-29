<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\Admin\AdminZoneSetupManageController;
use App\Http\Controllers\Api\V1\Admin\AdminCommissionManageController;
use App\Http\Controllers\Api\V1\Admin\AdminProductTypeManageController;
use Illuminate\Support\Facades\Route;


/*---------------------  business-settings management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/business-operations/')->group(function () {
    // product type
    Route::group(['prefix' => 'product-type/', 'middleware' => 'permission:' . PermissionKey::ADMIN_STORE_TYPE_MANAGE->value], function () {
        Route::get('list', [AdminProductTypeManageController::class, 'allTypes']);
        Route::get('details/{id}', [AdminProductTypeManageController::class, 'typeDetails']);
        Route::post('update', [AdminProductTypeManageController::class, 'updateType']);
        Route::post('change-status', [AdminProductTypeManageController::class, 'changeStatus']);
    });

    // zone setup
    Route::prefix('zone/')->middleware(['permission:' . PermissionKey::ADMIN_GEO_AREA_MANAGE->value])->group(function () {
        Route::get('list', [AdminZoneSetupManageController::class, 'index']);
        Route::post('add', [AdminZoneSetupManageController::class, 'store']);
        Route::post('update', [AdminZoneSetupManageController::class, 'update']);
        Route::get('details/{id}', [AdminZoneSetupManageController::class, 'show']);
        Route::post('change-status', [AdminZoneSetupManageController::class, 'changeStatus']);
        Route::delete('remove/{id}', [AdminZoneSetupManageController::class, 'destroy']);
        Route::post('settings/update', [AdminZoneSetupManageController::class, 'updateZoneSetting']);
        Route::get('settings/details/{zone_id}', [AdminZoneSetupManageController::class, 'zoneSettingsDetails']);
    });

    // system settings  charge
    Route::match(['get','post'],'settings',[AdminCommissionManageController::class,'settings'])->middleware('permission:' . PermissionKey::ADMIN_COMMISSION_SETTINGS->value);

});
