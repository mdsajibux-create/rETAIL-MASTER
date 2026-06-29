<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\Admin\AdminCashCollectionController;
use App\Http\Controllers\Api\V1\HmacGenerateController;
use App\Http\Controllers\Api\V1\StripePaymentController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Modules\Wallet\app\Http\Controllers\Api\AdminWithdrawGatewayManageController;
use Modules\Wallet\app\Http\Controllers\Api\AdminWithdrawManageController;
use Modules\Wallet\app\Http\Controllers\Api\AdminWithdrawRequestManageController;
use Modules\Wallet\app\Http\Controllers\Api\AdminWithdrawSettingsController;
use Modules\Wallet\app\Http\Controllers\Api\SellerAndDeliverymanWithdrawController;
use Modules\Wallet\app\Http\Controllers\Api\WalletCommonController;
use Modules\Wallet\app\Http\Controllers\Api\WalletManageAdminController;

/* --------------------- Admin route start ------------------------- */
Route::middleware(['auth:sanctum'])->prefix('v1/admin/financial/')->group(function () {
    Route::group(['prefix' => 'wallet/', 'middleware' => 'permission:' . PermissionKey::ADMIN_WALLET_MANAGE->value], function () {
        Route::match(['get', 'post'], 'settings', [WalletManageAdminController::class, 'depositSettings'])->middleware(['permission:' . PermissionKey::ADMIN_WALLET_SETTINGS->value]);
        Route::get('list', [WalletManageAdminController::class, 'index']);
        Route::post('status', [WalletManageAdminController::class, 'status']);
        Route::post('deposit', [WalletManageAdminController::class, 'depositCreateByAdmin']);
        Route::get('transactions', [WalletManageAdminController::class, 'transactionRecords'])->middleware(['permission:' . PermissionKey::ADMIN_WALLET_TRANSACTION->value]);
        Route::post('transactions-status', [WalletManageAdminController::class, 'transactionStatus']);
        Route::post('transactions-payment-status-change', [WalletManageAdminController::class, 'transactionPaymentStatusChange']);
    });

    Route::group(['prefix' => 'withdraw/', 'middleware' => 'permission:' . PermissionKey::ADMIN_FINANCIAL_WITHDRAW_MANAGE_SETTINGS->value], function () {

        Route::group(['middleware' => 'permission:' . PermissionKey::ADMIN_FINANCIAL_WITHDRAW_MANAGE_SETTINGS->value], function () {
            Route::match(['get', 'post'], 'settings', [AdminWithdrawSettingsController::class, 'withdrawSettings']);
        });

        // gateway manage
        Route::group(['middleware' => 'permission:' . PermissionKey::ADMIN_WITHDRAW_METHOD_MANAGEMENT->value], function () {
            Route::get('gateway-list', [AdminWithdrawGatewayManageController::class, 'withdrawGatewayList']);
            Route::post('gateway-add', [AdminWithdrawGatewayManageController::class, 'withdrawGatewayAdd']);
            Route::get('gateway-details/{id?}', [AdminWithdrawGatewayManageController::class, 'withdrawGatewayDetails']);
            Route::post('gateway-update', [AdminWithdrawGatewayManageController::class, 'withdrawGatewayUpdate']);
            Route::delete('gateway-delete/{id}', [AdminWithdrawGatewayManageController::class, 'withdrawGatewayDelete']);
            Route::post('gateway-change-status', [AdminWithdrawGatewayManageController::class, 'withdrawGatewayChangeStatus']);
        });

        // all manage
        Route::group(['middleware' => 'permission:' . PermissionKey::ADMIN_FINANCIAL_WITHDRAW_MANAGE_HISTORY->value], function () {
            Route::get('/', [AdminWithdrawManageController::class, 'withdrawAllList']);
            Route::get('details/{id}', [AdminWithdrawManageController::class, 'withdrawDetails']);
        });

        // request manage
        Route::group(['middleware' => 'permission:' . PermissionKey::ADMIN_FINANCIAL_WITHDRAW_MANAGE_REQUEST->value], function () {
            Route::get('request-list', [AdminWithdrawRequestManageController::class, 'withdrawRequestList']);
            Route::post('request-approve', [AdminWithdrawRequestManageController::class, 'withdrawRequestApprove']);
            Route::post('request-reject', [AdminWithdrawRequestManageController::class, 'withdrawRequestReject']);
        });

    });

     Route::group(['prefix' => '/', 'middleware' => 'permission:' . PermissionKey::ADMIN_FINANCIAL_WITHDRAW_MANAGE_SETTINGS->value], function () {
        Route::match(['get', 'post'], '/cash-collection', [AdminCashCollectionController::class, 'collectCash'])->middleware('permission:' . PermissionKey::ADMIN_FINANCIAL_COLLECT_CASH->value);
    });
});


// customer deliveryman and seller
Route::middleware(['auth:sanctum','detect.platform'])->prefix('v1')->group(function () {
    // deliveryman wallet
    Route::group(['prefix' => 'deliveryman/'], function () {
        Route::group(['prefix' => 'wallet/'], function () {
            Route::get('/', [WalletCommonController::class, 'myWalletInfo']);
            Route::post('deposit', [WalletCommonController::class, 'depositCreate']);
            Route::get('transactions', [WalletCommonController::class, 'transactionRecords']);
            Route::get('wallet-history', [WalletCommonController::class, 'walletHistory']);
        });
        Route::group(['prefix' => 'withdraw/'], function () {
            Route::get('/', [SellerAndDeliverymanWithdrawController::class, 'withdrawAllList']);
            Route::get('details/{id?}', [SellerAndDeliverymanWithdrawController::class, 'withdrawDetails']);
            Route::post('withdraw-request', [SellerAndDeliverymanWithdrawController::class, 'withdrawRequest']);
        });
    });

    // Customer Wallet
    Route::group(['prefix' => 'customer/wallet','middleware' => ['check.email.verification.option:customer']], function () {
        Route::get('/', [WalletCommonController::class, 'myWalletInfo']);
        Route::post('deposit', [WalletCommonController::class, 'depositCreate']);
        Route::get('transactions', [WalletCommonController::class, 'transactionRecords']);
    });

    //wallet common routes
    Route::group(['prefix' => '/wallet'], function () {
        Route::post('/payment-status-update', [WalletCommonController::class, 'paymentStatusUpdate'])->middleware('verify.hmac');
        Route::get('/generate-hmac', [HmacGenerateController::class, 'generateHmac']);
        Route::post('/create-stripe-session', [StripePaymentController::class, 'createCheckoutSessionForWallet']);
        Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhookForWallet']);
    });

    Route::get('withdraw/gateway-method-list', [SellerAndDeliverymanWithdrawController::class, 'withdrawGatewayList']);
});
