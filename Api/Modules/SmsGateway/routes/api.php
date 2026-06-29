<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\SmsGateway\app\Http\Controllers\Api\V1\SmsProviderController;
use Modules\SmsGateway\app\Http\Controllers\Api\V1\UserOtpController;

//  Admin Sms settings
Route::middleware(['auth:sanctum'])->prefix('v1/admin/sms-provider/')->group(function () {
    Route::prefix('settings/')->middleware(['permission:' . PermissionKey::ADMIN_SMS_GATEWAY_SETTINGS->value])->group(function () {
        Route::post('update', [SmsProviderController::class, 'smsProviderSettingUpdate']);
        Route::match(['get', 'post'], 'update', [SmsProviderController::class, 'smsProviderSettingUpdate']);
        Route::post('status-update', [SmsProviderController::class, 'smsProviderStatusUpdate']);
        Route::match(['get', 'post'], 'otp-login-status', [SmsProviderController::class, 'smsProviderLoginStatus']);
    });
});

// global otp manage
Route::prefix('v1/otp-login/')->group(function () {
    Route::post('send', [UserOtpController::class, 'sendOtp']);
    Route::post('verify', [UserOtpController::class, 'verifyOtp']);
    Route::post('resend', [UserOtpController::class, 'resendOtp']);
});
