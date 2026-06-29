<?php

use App\Enums\PermissionKey;
use Illuminate\Support\Facades\Route;
use Modules\Wallet\app\Http\Controllers\Api\SellerAndDeliverymanWithdrawController;
use Modules\Wallet\app\Http\Controllers\Api\WalletCommonController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // // admin wallet manage route in admin-api.php file included

    // seller wallet routes
    Route::prefix('seller/store/financial/')->middleware(['permission:' . PermissionKey::SELLER_STORE_FINANCIAL_WALLET->value])->group(function () {
        // seller wallet
        Route::group(['prefix' => 'wallet/'], function () {
            Route::get('/', [WalletCommonController::class, 'myWallet']);
            Route::post('deposit', [WalletCommonController::class, 'depositCreate']);
            Route::get('transactions', [WalletCommonController::class, 'transactionRecords']);
        });
        // withdraw history
        Route::group(['prefix' => 'withdraw/', 'middleware' => 'permission:' . PermissionKey::SELLER_STORE_FINANCIAL_WITHDRAWALS->value], function () {
            Route::get('/', [SellerAndDeliverymanWithdrawController::class, 'withdrawAllList']);
            Route::get('details/{id?}', [SellerAndDeliverymanWithdrawController::class, 'withdrawDetails']);
            Route::post('withdraw-request', [SellerAndDeliverymanWithdrawController::class, 'withdrawRequest']);
        });
    });

    // deliveryman wallet routes
    Route::group(['prefix' => 'deliveryman/'], function () {
        Route::group(['prefix' => 'wallet/'], function () {
            Route::get('/', [WalletCommonController::class, 'myWallet']);
            Route::post('deposit', [WalletCommonController::class, 'depositCreate']);
            Route::get('transactions', [WalletCommonController::class, 'transactionRecords']);
            Route::get('wallet-history', [WalletCommonController::class, 'walletHistory']);
        });
//        Route::group(['prefix' => 'withdraw/', 'middleware' => 'permission:' . PermissionKey::DELIVERYMAN_FINANCIAL_WITHDRAWALS->value], function () {
        Route::group(['prefix' => 'withdraw/'], function () {
            Route::get('/', [SellerAndDeliverymanWithdrawController::class, 'withdrawAllList']);
            Route::get('details/{id?}', [SellerAndDeliverymanWithdrawController::class, 'withdrawDetails']);
            Route::post('withdraw-request', [SellerAndDeliverymanWithdrawController::class, 'withdrawRequest']);
        });
    });

    // Customer Wallet
    Route::group(['prefix' => 'customer/wallet'], function () {
        Route::get('/', [WalletCommonController::class, 'myWallet']);
        Route::post('deposit', [WalletCommonController::class, 'depositCreate']);
        Route::get('transactions', [WalletCommonController::class, 'transactionRecords']);
        // wallet payment status update for common
        Route::post('payment-status-update', [WalletCommonController::class, 'paymentStatusUpdate']);
    });


    // withdraw all method lists
    Route::get('withdraw/gateway-method-list', [SellerAndDeliverymanWithdrawController::class, 'withdrawGatewayList']);

});
