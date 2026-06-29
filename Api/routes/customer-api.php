<?php

use App\Http\Controllers\Api\V1\Customer\CustomerAddressManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerBlogController;
use App\Http\Controllers\Api\V1\Customer\CustomerManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderRefundController;
use App\Http\Controllers\Api\V1\Customer\CustomerProductQueryController;
use App\Http\Controllers\Api\V1\Customer\CustomerReviewManageController;
use App\Http\Controllers\Api\V1\Customer\OrderPaymentController;
use App\Http\Controllers\Api\V1\Customer\WishListManageController;
use App\Http\Controllers\Api\V1\HmacGenerateController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationManageController;
use Illuminate\Support\Facades\Route;


/*--------------------- Route without auth  ----------------------------*/
Route::group(['namespace' => 'Api\V1', 'prefix' => 'customer/'], function () {
    // For customer register and login
    Route::post('registration', [CustomerManageController::class, 'registerCustomer']);
    Route::post('login', [CustomerManageController::class, 'loginCustomer']);
    Route::post('forget-password', [CustomerManageController::class, 'sendPasswordResetToken']);
    Route::post('verify-token', [CustomerManageController::class, 'verifyPasswordResetToken']);
    Route::patch('reset-password', [CustomerManageController::class, 'resetPassword']);
    Route::post('refresh-token', [CustomerManageController::class, 'refreshToken']);
});

Route::group([
    'namespace' => 'Api\V1', 'prefix' => 'customer/', 'middleware' =>
    ['auth:api_customer', 'check.customer.account.status', 'detect.platform']
], function () {

    // media manage
    Route::group(['prefix' => 'media-upload'], function () {
        Route::post('/store', [MediaController::class, 'mediaUpload']);
        Route::get('/load-more', [MediaController::class, 'load_more']);
        Route::post('/alt', [MediaController::class, 'alt_change']);
        Route::post('/delete', [MediaController::class, 'delete_media']);
    });

    Route::group(['middleware' => ['check.email.verification.option:customer']], function () {
         Route::get('/dashboard', [CustomerManageController::class, 'getDashboard']);

        Route::group(['prefix' => 'profile/'], function () {
            Route::get('/', [CustomerManageController::class, 'customerProfile']);
            Route::post('/update', [CustomerManageController::class, 'updateCustomerProfile']);
            Route::post('/change-email', [CustomerManageController::class, 'updateCustomerEmail']);
            Route::patch('/change-password', [CustomerManageController::class, 'changeCustomerPassword']);
            Route::patch('/activate-deactivate', [CustomerManageController::class, 'updateAccountStatus']);
            Route::get('/change-activity-notification-status', [CustomerManageController::class, 'toggleActivityNotification']);
            Route::get('/change-marketing-email-status', [CustomerManageController::class, 'toggleMarketingEmail']);
            Route::get('/delete', [CustomerManageController::class, 'deleteCustomerAccount']);
        });

        Route::group(['prefix' => 'blog/'], function () {
            Route::post('comment', [CustomerBlogController::class, 'addComment']);
            Route::post('comment-reaction', [CustomerBlogController::class, 'addReaction']);
        });

        Route::group(['prefix' => 'address/'], function () {
            Route::post('add', [CustomerAddressManageController::class, 'addAddress']);
            Route::post('update', [CustomerAddressManageController::class, 'updateAddress']);
            Route::get('customer-addresses', [CustomerAddressManageController::class, 'listAddresses']);
            Route::get('customer-address', [CustomerAddressManageController::class, 'getAddressById']);
            Route::post('make-default', [CustomerAddressManageController::class, 'defaultAddress']);
            Route::delete('remove/{id}', [CustomerAddressManageController::class, 'deleteAddress']);
        });


        // Notifications manage
        Route::group(['prefix' => 'notifications'], function () {
            Route::get('/', [NotificationManageController::class, 'listNotifications']);
            Route::patch('/read', [NotificationManageController::class, 'markAsRead']);
        });

        Route::group(['prefix' => 'wishlist'], function () {
            Route::get('/', [WishListManageController::class, 'wishlists']);
            Route::post('/store', [WishListManageController::class, 'addToWishlist']);
            Route::delete('/remove', [WishListManageController::class, 'removeFromWishlist']);
        });

        // order manage
        Route::group(['prefix' => 'orders/'], function () {
            Route::get('invoice', [CustomerOrderController::class, 'orderInvoice']);
            Route::post('cancel-order', [CustomerOrderController::class, 'cancelOrder']);
            Route::post('check-coupon', [CustomerOrderController::class, 'checkCoupon']);
            Route::post('request-refund', [CustomerOrderRefundController::class, 'orderRefundRequest']);
            Route::get('{order_id?}', [CustomerOrderController::class, 'myOrders']);
        });

        Route::group(['prefix' => 'review/'], function () {
            Route::get('/', [CustomerReviewManageController::class, 'index']);
            Route::post('add', [CustomerReviewManageController::class, 'submitReview']);
            Route::post('reaction', [CustomerReviewManageController::class, 'react']);
        });

        Route::group(['prefix' => 'product-query/'], function () {
            Route::post('ask-question', [CustomerProductQueryController::class, 'askQuestion']);
        });
    });

    // customer place order payment update
    Route::put('orders/payment-status-update', [OrderPaymentController::class, 'orderPaymentStatusUpdate'])->middleware('verify.hmac');
    Route::get('generate-hmac', [HmacGenerateController::class, 'generateHmac']);

    // customer verify email
    Route::post('send-verification-email', [CustomerManageController::class, 'sendVerificationEmail']);
    Route::post('verify-email', [CustomerManageController::class, 'verifyEmail']);
    Route::post('resend-verification-email', [CustomerManageController::class, 'resendVerificationEmail']);
});

