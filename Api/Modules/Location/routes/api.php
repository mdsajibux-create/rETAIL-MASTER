<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Location\app\Http\Controllers\Api\V1\AreaController;
use Modules\Location\app\Http\Controllers\Api\V1\CityController;
use Modules\Location\app\Http\Controllers\Api\V1\LocationController;
use Modules\Location\app\Http\Controllers\Api\V1\StateController;


/*--------------------- Order management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    // States
    Route::middleware(['permission:' . PermissionKey::ADMIN_STATE_LIST->value])->group(function () {
        Route::get('states', [StateController::class, 'states']);
        Route::post('state/add', [StateController::class, 'statesAdd']);
        Route::put('state/update', [StateController::class, 'statesUpdate']);
        Route::patch('state/update-status', [StateController::class, 'statesUpdateStatus']);
        Route::get('state/details/{id?}', [StateController::class, 'statesDetails']);
        Route::delete('state/delete', [StateController::class, 'statesDelete']);
    });

    // Cities
    Route::middleware(['permission:' . PermissionKey::ADMIN_CITY_LIST->value])->group(function () {
        Route::get('cities', [CityController::class, 'cities']);
        Route::post('city/add', [CityController::class, 'citiesAdd']);
        Route::put('city/update', [CityController::class, 'citiesUpdate']);
        Route::patch('city/update-status', [CityController::class, 'citiesUpdateStatus']);
        Route::get('city/details/{id?}', [CityController::class, 'citiesDetails']);
        Route::delete('city/delete', [CityController::class, 'citiesDelete']);
    });

    // Areas
    Route::middleware(['permission:' . PermissionKey::ADMIN_AREA_LIST->value])->group(function () {
        Route::get('areas', [AreaController::class, 'areas']);
        Route::post('area/add', [AreaController::class, 'areaAdd']);
        Route::put('area/update', [AreaController::class, 'areaUpdate']);
        Route::patch('area/update-status', [AreaController::class, 'areaUpdateStatus']);
        Route::get('area/details/{id?}', [AreaController::class, 'areaDetails']);
        Route::delete('area/delete', [AreaController::class, 'areaDelete']);
    });

});


/*
|--------
| PUBLIC
|--------
*/
Route::prefix('v1/')->group(function () {
    Route::get('states',                    [LocationController::class, 'states']);
    Route::get('cities/{state_id?}',     [LocationController::class, 'citiesByState']);
    Route::get('areas/{city_id}',       [LocationController::class, 'areasByCity']);
});