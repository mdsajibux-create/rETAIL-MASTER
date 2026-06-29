<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Deliveryman\app\Http\Controllers\Api\V1\AdminDeliverymanManageController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin/deliveryman/')->group(function () {
    Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_DELIVERYMAN_MANAGE_LIST->value]], function () {
        Route::get('list', [AdminDeliverymanManageController::class, 'index']);
        Route::get('list-dropdown', [AdminDeliverymanManageController::class, 'deliverymanDropdownList']);
        Route::get('request', [AdminDeliverymanManageController::class, 'deliverymanRequest'])->middleware(['permission:' . PermissionKey::ADMIN_DELIVERYMAN_REQUEST->value]);
        Route::post('add', [AdminDeliverymanManageController::class, 'store']);
        Route::post('change-password', [AdminDeliverymanManageController::class, 'changePassword']);
        Route::get('details/{id}', [AdminDeliverymanManageController::class, 'show']);
        Route::post('update', [AdminDeliverymanManageController::class, 'update']);
        Route::post('change-status', [AdminDeliverymanManageController::class, 'changeStatus']);
        Route::post('verification', [AdminDeliverymanManageController::class, 'deliverymanVerification']);
        Route::post('handle-request', [AdminDeliverymanManageController::class, 'handleRequest']);
        Route::delete('remove/{id}', [AdminDeliverymanManageController::class, 'destroy']);
        Route::get('trash-list', [AdminDeliverymanManageController::class, 'getTrashList'])->middleware('permission:'.PermissionKey::ADMIN_DELIVERYMAN_TRASH_MANAGEMENT->value);
        Route::post('trash-restore', [AdminDeliverymanManageController::class, 'restoreTrashed'])->middleware('permission:'.PermissionKey::ADMIN_DELIVERYMAN_TRASH_MANAGEMENT->value);
        Route::post('trash-delete', [AdminDeliverymanManageController::class, 'deleteTrashed'])->middleware('permission:'.PermissionKey::ADMIN_DELIVERYMAN_TRASH_MANAGEMENT->value);
        Route::get('history/{id}', [AdminDeliverymanManageController::class, 'deliverymanDashboard']);
    });

    //vehicle-types
    Route::prefix('vehicle-types/')->middleware(['permission:' . PermissionKey::ADMIN_DELIVERYMAN_VEHICLE_TYPE->value])->group(function () {
        Route::get('list', [AdminDeliverymanManageController::class, 'indexVehicle']);
        Route::get('list-dropdown', [AdminDeliverymanManageController::class, 'vehicleTypeDropdown']);
        Route::post('add', [AdminDeliverymanManageController::class, 'storeVehicle']);
        Route::get('details/{id}', [AdminDeliverymanManageController::class, 'showVehicle']);
        Route::post('update', [AdminDeliverymanManageController::class, 'updateVehicle']);
        Route::post('change-status', [AdminDeliverymanManageController::class, 'changeVehicleStatus']);
        Route::delete('remove/{id}', [AdminDeliverymanManageController::class, 'destroyVehicle']);
    });

});