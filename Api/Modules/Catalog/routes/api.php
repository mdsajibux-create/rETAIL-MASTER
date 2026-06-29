<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Catalog\app\Http\Controllers\Api\V1\AdminUnitManageController;
use Modules\Catalog\app\Http\Controllers\Api\V1\DynamicFieldsManageController;
use Modules\Catalog\app\Http\Controllers\Api\V1\DynamicFieldsOptionManageController;
use Modules\Catalog\app\Http\Controllers\Api\V1\ProductAttributeController;
use Modules\Catalog\app\Http\Controllers\Api\V1\ProductBrandController;
use Modules\Catalog\app\Http\Controllers\Api\V1\ProductCategoryController;
use Modules\Catalog\app\Http\Controllers\Api\V1\TagManageController;
use Modules\Catalog\app\Http\Controllers\Api\V1\SliderManageController;

/*--------------------- Catalog management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    // Product Category Routing
    Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_CATEGORY_LIST->value]], function () {
        Route::get('product-categories/list', [ProductCategoryController::class, 'listProductCategories']);
        Route::post('product-categories/add', [ProductCategoryController::class, 'createProductCategory']);
        Route::get('product-categories/details/{id}', [ProductCategoryController::class, 'getProductCategoryById']);
        Route::post('product-categories/update', [ProductCategoryController::class, 'createProductCategory']);
        Route::patch('product-categories/change-status', [ProductCategoryController::class, 'changeProductCategoryStatus']);
        Route::post('product-categories/remove', [ProductCategoryController::class, 'deleteProductCategories']);
    });

    //Product Attribute Management
    Route::group(['prefix' => 'attribute/', 'middleware/' => ['permission:' . PermissionKey::PRODUCT_ATTRIBUTE_ADD->value]], function () {
        Route::get('list', [ProductAttributeController::class, 'listAttributes']);
        Route::get('details/{id}', [ProductAttributeController::class, 'getAttributeById']);
        Route::get('type-wise', [ProductAttributeController::class, 'typeWiseAttributes']);
        Route::post('add', [ProductAttributeController::class, 'createAttribute']);
        Route::post('update', [ProductAttributeController::class, 'updateAttribute']);
        Route::patch('change-status', [ProductAttributeController::class, 'changeAttributeStatus']);
        Route::delete('remove/{id}', [ProductAttributeController::class, 'deleteAttribute']);
    });

    // Product Brand Routing
    Route::group(['prefix' => 'brand/'], function () {
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_BRAND_LIST->value]], function () {
            Route::get('list', [ProductBrandController::class, 'listBrands']);
            Route::post('add', [ProductBrandController::class, 'createBrand']);
            Route::put('update', [ProductBrandController::class, 'updateBrand']);
            Route::get('details/{id}', [ProductBrandController::class, 'getBrandById']);
            Route::patch('change-status', [ProductBrandController::class, 'changeBrandStatus']);
            Route::post('remove', [ProductBrandController::class, 'deleteBrands']);
        });
    });

    // Tag manage
    Route::group(['prefix' => 'tag/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_TAG_LIST->value]], function () {
        Route::get('list', [TagManageController::class, 'listTags']);
        Route::post('add', [TagManageController::class, 'createTag']);
        Route::get('details/{id}', [TagManageController::class, 'getTagById']);
        Route::post('update', [TagManageController::class, 'updateTag']);
        Route::post('remove', [TagManageController::class, 'deleteTags']);
    });

    // Unit manage
    Route::group(['prefix' => 'unit/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_UNIT_LIST->value]], function () {
        Route::get('list', [AdminUnitManageController::class, 'listUnits']);
        Route::post('add', [AdminUnitManageController::class, 'createUnit']);
        Route::get('details/{id}', [AdminUnitManageController::class, 'getUnitById']);
        Route::post('update', [AdminUnitManageController::class, 'updateUnit']);
        Route::delete('remove/{id}', [AdminUnitManageController::class, 'deleteUnit']);
    });

    // dynamic fields manage
    Route::group(['prefix' => 'dynamic-fields/', 'middleware' => ['permission:' . PermissionKey::ADMIN_DYNAMIC_FIELDS->value]], function () {
        Route::get('/', [DynamicFieldsManageController::class, 'getDynamicOptionForProduct']);
        Route::get('list', [DynamicFieldsManageController::class, 'list']);
        Route::post('add', [DynamicFieldsManageController::class, 'createDynamicField']);
        Route::get('details/{id}', [DynamicFieldsManageController::class, 'getDynamicFieldById']);
        Route::post('update', [DynamicFieldsManageController::class, 'updateDynamicField']);
        Route::delete('remove/{id}', [DynamicFieldsManageController::class, 'deleteDynamicField']);
        Route::patch('change-status', [DynamicFieldsManageController::class, 'changeDynamicFieldStatus']);
    });

    // dynamic fields values manage
    Route::group(['prefix' => 'dynamic-fields/options/', 'middleware' => ['permission:' . PermissionKey::ADMIN_DYNAMIC_FIELDS->value]], function () {
        Route::get('list', [DynamicFieldsOptionManageController::class, 'list']);
        Route::post('add', [DynamicFieldsOptionManageController::class, 'createDynamicFieldOption']);
        Route::get('details/{id}', [DynamicFieldsOptionManageController::class, 'getDynamicFieldByIdOption']);
        Route::post('update', [DynamicFieldsOptionManageController::class, 'updateDynamicFieldOption']);
        Route::delete('remove/{id}', [DynamicFieldsOptionManageController::class, 'deleteDynamicFieldOption']);
    });

    // Slider manage
    Route::group(['prefix' => 'slider/', 'middleware' => ['permission:' . PermissionKey::ADMIN_SLIDER_MANAGE_LIST->value]], function () {
        Route::get('list', [SliderManageController::class, 'listSliders']);
        Route::post('add', [SliderManageController::class, 'createSlider']);
        Route::get('details/{id}', [SliderManageController::class, 'getSliderById']);
        Route::post('update', [SliderManageController::class, 'updateSlider']);
        Route::patch('change-status', [SliderManageController::class, 'changeSliderStatus']);
        Route::delete('remove/{id}', [SliderManageController::class, 'deleteSlider']);
    });

});