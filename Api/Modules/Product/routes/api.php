<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Product\app\Http\Controllers\Api\V1\AdminBranchProductStockTransferController;
use Modules\Product\app\Http\Controllers\Api\V1\AdminInventoryManageController;
use Modules\Product\app\Http\Controllers\Api\V1\AdminProductManageController;
use Modules\Product\app\Http\Controllers\Api\V1\BranchProductManageController;
use Modules\Product\app\Http\Controllers\Api\V1\BranchProductStockManageController;
use Modules\Product\app\Http\Controllers\Api\V1\BranchProductStockTransferController;
use Modules\Product\app\Http\Controllers\Api\V1\ProductInventoryController;

/*--------------------- Product management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/product/')->group(function () {
    // Product Inventory management routes
    Route::group(['prefix' => 'inventory', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_INVENTORY->value]], function () {
        Route::get('/', [AdminInventoryManageController::class, 'allInventories']);
        Route::post('remove', [AdminInventoryManageController::class, 'deleteInventory']);
    });

    // Product management routes
    Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCTS_MANAGE->value]], function () {
        Route::get('select-list', [BranchProductManageController::class, 'selectProductsAdmin']);
        Route::get('list', [AdminProductManageController::class, 'listProducts']);
        Route::post('add', [AdminProductManageController::class, 'createProduct']);
        Route::patch('featured-add', [AdminProductManageController::class, 'addToFeatured']);
        Route::get('details/{slug}', [AdminProductManageController::class, 'getProductBySlug']);
        Route::put('update', [AdminProductManageController::class, 'updateProduct']);
        Route::delete('remove/{id?}', [AdminProductManageController::class, 'deleteProduct']);
        Route::get('trash-list', [AdminProductManageController::class, 'getProductTrashList'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCTS_TRASH_MANAGEMENT->value);
        Route::post('trash-restore', [AdminProductManageController::class, 'restoreProductTrashed'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCTS_TRASH_MANAGEMENT->value);
        Route::post('trash-delete', [AdminProductManageController::class, 'deleteProductTrashed'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCTS_TRASH_MANAGEMENT->value);
        Route::post('export', [AdminProductManageController::class, 'exportProducts'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCT_PRODUCT_BULK_IMPORT->value);
        Route::post('import', [AdminProductManageController::class, 'importProducts'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCT_PRODUCT_BULK_IMPORT->value);
        Route::patch('change-status', [AdminProductManageController::class, 'changeProductStatus']);
        Route::get('stock-report', [AdminProductManageController::class, 'lowOrOutOfStockProducts'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCT_STOCK_REPORT->value);
        Route::get('{product_slug}', [AdminProductManageController::class, 'productDetails']);
    });

    // stock add & update management
    Route::group(['prefix'     => 'stock/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCTS_STOCK_MANAGE->value]], function () {
        Route::get('list',            [BranchProductStockManageController::class, 'listStock']);
        Route::get('details/{id?}',         [BranchProductStockManageController::class, 'getStockByIdAdmin']);
        Route::post('add',            [BranchProductStockManageController::class, 'addStock']);
        Route::post('update',         [BranchProductStockManageController::class, 'updateStock']);
    });

    // product stock TRANSFERS approved admin routes
    Route::group(['prefix'     => 'stock/transfer/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCTS_STOCK_TRANSFER_MANAGE->value] ], function () {
        Route::get('list',              [AdminBranchProductStockTransferController::class, 'listTransfers']);
        Route::get('details/{id}',           [AdminBranchProductStockTransferController::class, 'getTransferById']);
        Route::post('create',           [AdminBranchProductStockTransferController::class, 'createTransfer']);
        Route::post('update-status',    [AdminBranchProductStockTransferController::class, 'updateStatus']);
    });

});


/*--------------------- Branch by Product management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/branch/product/')->group(function () {
    // product list
    Route::group(['middleware' => ['permission:' . PermissionKey::BRANCH_PRODUCT_LIST->value]], function () {
      Route::get('list', [BranchProductManageController::class, 'listProducts']);
      Route::get('select-list', [BranchProductManageController::class, 'selectProducts']);
    });

    // Product Inventory routes
    Route::group(['middleware' => ['permission:' . PermissionKey::BRANCH_PRODUCT_INVENTORY->value]], function () {
        Route::get('inventory', [ProductInventoryController::class, 'inventory']);
    });

    // stock add & update management
    Route::group(['prefix'     => 'stock/', 'middleware' => ['permission:' . PermissionKey::BRANCH_PRODUCT_STOCK_MANAGE->value]], function () {
        Route::get('list',            [BranchProductStockManageController::class, 'listStock']);
        Route::get('details/{id?}',         [BranchProductStockManageController::class, 'getStockById']);
        Route::post('add',            [BranchProductStockManageController::class, 'addStock']);
        Route::post('update',         [BranchProductStockManageController::class, 'updateStock']);
    });

    // stock TRANSFERS
    Route::group(['prefix'     => 'stock/transfer/', 'middleware' => ['permission:' . PermissionKey::BRANCH_PRODUCT_STOCK_TRANSFER_MANAGE->value] ], function () {
        Route::get('list',              [BranchProductStockTransferController::class, 'listTransfers']);
        Route::get('details/{id}',           [BranchProductStockTransferController::class, 'getTransferById']);
        Route::post('create',           [BranchProductStockTransferController::class, 'createTransfer']);
        Route::post('update-status',    [BranchProductStockTransferController::class, 'updateStatus']);
    });

});