<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentGateways\app\Http\Controllers\PaymentGatewaysController;



Route::group([], function () {
    Route::resource('paymentgateways', PaymentGatewaysController::class)->names('paymentgateways');
});
