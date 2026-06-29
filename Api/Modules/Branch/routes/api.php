<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Branch\app\Http\Controllers\Api\V1\AdminBranchManageController;


/*--------------------- Branch management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    Route::group(['prefix' => 'branch/'], function () {
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_STORE_LIST->value]], function () {
            Route::get('list', [AdminBranchManageController::class, 'branches']);
            Route::get('details/{id}', [AdminBranchManageController::class, 'getBranchById']);
        });

        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_BRANCH_ADD->value]], function () {
            Route::post('add', [AdminBranchManageController::class, 'createBranch']);
            Route::post('update', [AdminBranchManageController::class, 'updateBranch']);
            Route::patch('change-status', [AdminBranchManageController::class, 'changeBranchStatus']);
            Route::patch('set-web-branch', [AdminBranchManageController::class, 'setWebBranch']);
            Route::delete('delete/{id}', [AdminBranchManageController::class, 'deleteBranch']);
            Route::get('deleted-records', [AdminBranchManageController::class, 'deletedBranchRecords']);
            Route::get('trash-list', [AdminBranchManageController::class, 'branchTrashList'])->middleware('permission:' . PermissionKey::ADMIN_STORE_TRASH_MANAGEMENT->value);
            Route::post('trash-restore', [AdminBranchManageController::class, 'restoreStoreTrashed'])->middleware('permission:' . PermissionKey::ADMIN_STORE_TRASH_MANAGEMENT->value);
            Route::post('trash-delete', [AdminBranchManageController::class, 'deleteStoreTrashed'])->middleware('permission:' . PermissionKey::ADMIN_STORE_TRASH_MANAGEMENT->value);
        });
    });

});