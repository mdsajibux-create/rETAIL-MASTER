<?php

use App\Http\Controllers\Api\V1\Customer\CustomerAddressManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerBlogController;
use App\Http\Controllers\Api\V1\Customer\CustomerManageController as CustomerManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderRefundController;
use App\Http\Controllers\Api\V1\Customer\CustomerProductQueryController;
use App\Http\Controllers\Api\V1\Customer\CustomerReviewManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerSupportTicketManageController;
use App\Http\Controllers\Api\V1\Customer\WishListManageController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationManageController;
use App\Http\Controllers\Customer\OrderPaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api\V1', 'prefix' => 'customer/', 'middleware' => ['auth:api_customer', 'check.customer.account.status']], function () {

    Route::get('/', [CustomerManageController::class, 'getDashboard']);
    // media manage
    Route::group(['prefix' => 'media-upload'], function () {
        Route::post('/store', [MediaController::class, 'mediaUpload']);
        Route::get('/load-more', [MediaController::class, 'load_more']);
        Route::post('/alt', [MediaController::class, 'alt_change']);
        Route::post('/delete', [MediaController::class, 'delete_media']);
    });
    Route::group(['middleware' => ['check.email.verification.option']], function () {
        Route::group(['prefix' => 'profile/'], function () {
            Route::get('/', [CustomerManageController::class, 'getProfile']);
            Route::post('/update', [CustomerManageController::class, 'updateProfile']);
            Route::post('/change-email', [CustomerManageController::class, 'updateEmail']);
            Route::post('/change-password', [CustomerManageController::class, 'changePassword']);
            Route::post('/activate-deactivate', [CustomerManageController::class, 'activeDeactiveAccount']);
            Route::get('/change-activity-notification-status', [CustomerManageController::class, 'activityNotificationToggle']);
            Route::get('/change-marketing-email-status', [CustomerManageController::class, 'marketingEmailToggle']);
            Route::get('/delete', [CustomerManageController::class, 'deleteAccount']);
        });
        Route::group(['prefix' => 'blog/'], function () {
            Route::post('comment', [CustomerBlogController::class, 'comment']);
            Route::post('comment-reaction', [CustomerBlogController::class, 'react']);
        });
        Route::group(['prefix' => 'address/'], function () {
            Route::post('add', [CustomerAddressManageController::class, 'store']);
            Route::post('update', [CustomerAddressManageController::class, 'update']);
            Route::get('customer-addresses', [CustomerAddressManageController::class, 'index']);
            Route::get('customer-address', [CustomerAddressManageController::class, 'show']);
            Route::post('make-default', [CustomerAddressManageController::class, 'defaultAddress']);
            Route::delete('remove/{id}', [CustomerAddressManageController::class, 'destroy']);
        });
        Route::group(['prefix' => 'support-ticket'], function () {
            Route::get('list', [CustomerSupportTicketManageController::class, 'index']);
            Route::post('store', [CustomerSupportTicketManageController::class, 'store']);
            Route::post('update', [CustomerSupportTicketManageController::class, 'update']);
            Route::get('details/{ticket_id}', [CustomerSupportTicketManageController::class, 'show']);
            Route::get('resolve', [CustomerSupportTicketManageController::class, 'resolve']);
            Route::post('add-message', [CustomerSupportTicketManageController::class, 'addMessage']);
            Route::get('messages/{ticket_id}', [CustomerSupportTicketManageController::class, 'getTicketMessages']);
        });

        // Notifications manage
       Route::group(['prefix' => 'notifications'], function () {
            Route::get('/', [NotificationManageController::class, 'index']);
            Route::post('/read', [NotificationManageController::class, 'markAsRead']);
        });

        Route::group(['prefix' => 'wish-list'], function () {
            Route::get('list', [WishListManageController::class, 'getWishlist']);
            Route::post('store', [WishListManageController::class, 'addToWishlist']);
            Route::post('remove', [WishListManageController::class, 'removeFromWishlist']);
        });
        // order manage
        Route::group(['prefix' => 'orders/'], function () {
            Route::get('invoice', [CustomerOrderController::class, 'invoice']);
            Route::post('cancel-order', [CustomerOrderController::class, 'cancelOrder']);
            Route::get('check-coupon', [CustomerOrderController::class, 'checkCoupon']);
            Route::post('request-refund',[CustomerOrderRefundController::class,'orderRefundRequest']);
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
    Route::post('orders/payment-status-update', [OrderPaymentController::class, 'orderPaymentStatusUpdate']);

    // customer verify email
    Route::post('send-verification-email', [CustomerManageController::class, 'sendVerificationEmail']);
    Route::post('verify-email', [CustomerManageController::class, 'verifyEmail']);
    Route::post('resend-verification-email', [CustomerManageController::class, 'resendVerificationEmail']);
});
