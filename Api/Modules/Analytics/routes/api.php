<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Analytics\app\Http\Controllers\Api\V1\AdminReportAnalyticsManageController;

// report-analytics
Route::middleware(['auth:sanctum'])->prefix('v1/admin/report-analytics')->group(function () {
    Route::get('list', [AdminReportAnalyticsManageController::class, 'reportList'])->middleware('permission:' . PermissionKey::ADMIN_REPORT_ANALYTICS_ORDER->value);
    Route::get('order', [AdminReportAnalyticsManageController::class, 'orderReport'])->middleware('permission:' . PermissionKey::ADMIN_REPORT_ANALYTICS_ORDER->value);
    Route::get('transaction', [AdminReportAnalyticsManageController::class, 'transactionReport'])->middleware('permission:' . PermissionKey::ADMIN_REPORT_ANALYTICS_TRANSACTION->value);
});