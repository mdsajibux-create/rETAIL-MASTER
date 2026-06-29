<?php

use Illuminate\Support\Facades\Route;
use Modules\Feedback\app\Http\Controllers\Api\V1\AdminProductQueryManageController;
use Modules\Feedback\app\Http\Controllers\Api\V1\AdminReviewManageController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin/feedback-control/')->group(function () {
    Route::group(['prefix' => 'review/'], function () {
        Route::get('/', [AdminReviewManageController::class, 'index']);
        Route::post('approve', [AdminReviewManageController::class, 'approveReview']);
        Route::post('reject', [AdminReviewManageController::class, 'rejectReview']);
        Route::post('remove', [AdminReviewManageController::class, 'destroy']);
    });
    Route::group(['prefix' => 'questions/'], function () {
        Route::get('/', [AdminProductQueryManageController::class, 'getAllQueries']);
        Route::post('change-status', [AdminProductQueryManageController::class, 'changeStatus']);
        Route::post('remove', [AdminProductQueryManageController::class, 'destroy']);
    });
});