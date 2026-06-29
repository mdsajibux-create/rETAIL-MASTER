<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Promotion\app\Http\Controllers\Api\V1\AdminFlashSaleManageController;


/*--------------------- Promotions Management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/promotional/flash-deals/')->group(function () {
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PROMOTIONAL_FLASH_SALE_MANAGE->value]], function () {
        Route::get('list', [AdminFlashSaleManageController::class, 'listFlashSales']);
        Route::get('list-dropdown', [AdminFlashSaleManageController::class, 'getFlashSaleDropdown']);
        Route::post('add', [AdminFlashSaleManageController::class, 'createFlashSale']);
        Route::post('add-products', [AdminFlashSaleManageController::class, 'adminAddProductToFlashSale']);
        Route::get('all-products', [AdminFlashSaleManageController::class, 'listAllFlashSaleProducts']);
        Route::post('update-products', [AdminFlashSaleManageController::class, 'adminUpdateProductToFlashSale']);
        Route::get('details/{id}', [AdminFlashSaleManageController::class, 'getFlashSaleById']);
        Route::put('update', [AdminFlashSaleManageController::class, 'updateFlashSale']);
        Route::patch('/change-status', [AdminFlashSaleManageController::class, 'changeFlashSaleStatus']);
        Route::delete('remove/{id}', [AdminFlashSaleManageController::class, 'deleteFlashSale']);
        Route::post('deactivate', [AdminFlashSaleManageController::class, 'deactivateFlashSale']);
    });
});