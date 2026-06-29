<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\PaymentGateways\app\Http\Controllers\Api\BkashController;
use Modules\PaymentGateways\app\Http\Controllers\Api\NagadController;
use Modules\PaymentGateways\app\Http\Controllers\Api\NagadTwoController;
use Modules\PaymentGateways\app\Http\Controllers\Api\PaymentGatewaysController;
use Modules\PaymentGateways\app\Http\Controllers\Api\PaymentGatewaySettingsController;
use Modules\PaymentGateways\app\Http\Controllers\Api\SslcommerzController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\ComSiteGeneralController;

Route::middleware(['auth:sanctum', 'permission:' . PermissionKey::ADMIN_PAYMENT_SETTINGS->value,'detect.platform'])->prefix('v1')->group(function () {

    Route::group(['prefix' => 'admin/payment-gateways'], function () {
        Route::match(['GET', 'POST'], '/{gateway?}', [PaymentGatewaySettingsController::class, 'paymentGatewayUpdate']);
        Route::get('/', [PaymentGatewaySettingsController::class, 'paymentGatewayList']);
        Route::post('/update', [PaymentGatewaySettingsController::class, 'paymentGatewayUpdate']);
        Route::patch('/update-status', [PaymentGatewaySettingsController::class, 'paymentGatewayStatusUpdate']);
    });

    Route::group(['prefix' => 'admin/currency'], function () {
        Route::get('/', [PaymentGatewaySettingsController::class, 'listCurrency']);
        Route::post('/add', [PaymentGatewaySettingsController::class, 'createCurrency']);
        Route::get('details/{id}', [PaymentGatewaySettingsController::class, 'getCurrencyById']);
        Route::put('/update', [PaymentGatewaySettingsController::class, 'updateCurrency']);
        Route::delete('remove/{id}', [PaymentGatewaySettingsController::class, 'deleteCurrency']);
        // currency settings route
        Route::get('/settings', [PaymentGatewaySettingsController::class, 'currencySettingsGet']);
        Route::put('/settings/update', [PaymentGatewaySettingsController::class, 'currencySettingsUpdate']);
    });
});

Route::middleware('detect.platform')->group(function () {
    Route::group(['prefix' => 'v1/'], function () {
        // payment gateways lists
        Route::get('/currency-list', [ComSiteGeneralController::class, 'currencyList']);
        Route::get('currency-info', [PaymentGatewaysController::class, 'currencySettingsGet']);
        Route::get('payment-gateways', [PaymentGatewaysController::class, 'paymentGateways']);
    });
});


Route::middleware('detect.platform')->group(function () {
    Route::group(['prefix' => 'v1/customer/', 'middleware' => ['auth:api_customer', 'check.customer.account.status',]], function () {
         // bkash pay
        Route::post('/bkash/create-payment', [BkashController::class, 'createPayment']);
        Route::match(['get', 'post'], '/bkash/callback', [BkashController::class, 'callback']);
        Route::match(['get', 'post'], '/bkash/payment/status', [BkashController::class, 'paymentStatus']);

        // nagad pay
        Route::post('/nagad/create-payment', [NagadTwoController::class, 'createPayment']);
        Route::match(['get', 'post'], '/nagad/callback', [NagadTwoController::class, 'callback']);
        Route::post('nagad/payment/status', [NagadTwoController::class, 'paymentStatus']);

        // ── SSLCommerz (needs auth — customer initiates) ───
        Route::post('/sslcommerz/payment/initiate', [SslcommerzController::class, 'initiate']);
        Route::get('/sslcommerz/payment/status/{tranId}', [SslcommerzController::class, 'status']);
    });

    // SSLCOMMERZ CALLBACKS — NO AUTH (called by SSLCommerz server)
    Route::group(['prefix' => 'v1/sslcommerz',], function () {
        Route::post('/payment/ipn',     [SslcommerzController::class, 'ipn'])->name('payment.ipn');
        Route::match(['get', 'post'],'/payment/success', [SslcommerzController::class, 'paymentSuccess'])->name('payment.success');
        Route::match(['get', 'post'],'/payment/fail',    [SslcommerzController::class, 'paymentFail'])->name('payment.fail');
        Route::match(['get', 'post'],'/payment/cancel',  [SslcommerzController::class, 'paymentCancel'])->name('payment.cancel');
    });

});

