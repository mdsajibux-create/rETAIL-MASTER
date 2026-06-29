<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Integration\app\Http\Controllers\Api\V1\IntegrationController;

/*--------------------- Promotions Management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    // Flash Sale manage
    Route::group(['prefix' => 'integration/', 'middleware' => 'permission:' . PermissionKey::ADMIN_INTEGRATION_SETTINGS->value], function () {
        Route::get('', [IntegrationController::class, 'index']);
        Route::get('/{id}', [IntegrationController::class, 'getIntegration']);
        Route::post('/update', [IntegrationController::class, 'updateIntegration']);
        Route::post('/status-change', [IntegrationController::class, 'statusChange']);
    });
});