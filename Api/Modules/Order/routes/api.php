<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\Customer\PlaceOrderController;
use App\Http\Controllers\Api\V1\StripePaymentController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use Illuminate\Support\Facades\Route;
use Modules\Order\app\Http\Controllers\Api\V1\AdminOrderManageController;
use Modules\Order\app\Http\Controllers\Api\V1\AdminOrderRefundManageController;


/*--------------------- Order management ----------------------------*/
Route::middleware(['auth:sanctum'])->prefix('v1/admin/')->group(function () {
    // Orders & Reviews Manage
    Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_ORDERS_ALL->value]], function () {
        Route::group(['prefix' => 'orders/'], function () {
            Route::get('invoice', [AdminOrderManageController::class, 'invoice']);
            Route::patch('change-order-status', [AdminOrderManageController::class, 'changeOrderStatus']);
            Route::patch('change-payment-status', [AdminOrderManageController::class, 'changePaymentStatus']);
            Route::post('assign-deliveryman', [AdminOrderManageController::class, 'assignDeliveryMan']);
            Route::patch('cancel-order', [AdminOrderManageController::class, 'cancelOrder']);
            Route::get('refund-request', [AdminOrderRefundManageController::class, 'orderRefundRequest'])->middleware('permission:' . PermissionKey::ADMIN_ORDERS_RETURNED_OR_REFUND_REQUEST->value);
            Route::post('refund-request/handle', [AdminOrderRefundManageController::class, 'handleRefundRequest'])->middleware('permission:' . PermissionKey::ADMIN_ORDERS_RETURNED_OR_REFUND_REQUEST->value);

            Route::group(['prefix' => 'refund-reason/', 'middleware' => ['permission:' . PermissionKey::ADMIN_ORDERS_RETURNED_OR_REFUND_REASON->value]], function () {
                Route::get('list', [AdminOrderRefundManageController::class, 'allOrderRefundReason']);
                Route::post('add', [AdminOrderRefundManageController::class, 'createOrderRefundReason']);
                Route::get('details/{id}', [AdminOrderRefundManageController::class, 'showOrderRefundReason']);
                Route::post('update', [AdminOrderRefundManageController::class, 'updateOrderRefundReason']);
                Route::delete('remove/{id}', [AdminOrderRefundManageController::class, 'deleteOrderRefundReason']);
            });

            // Dynamic route should be last
            Route::get('{order_id?}', [AdminOrderManageController::class, 'allOrders']);
        });
    });
});



/*--------------------- Customer order routes ---------------------*/
Route::prefix('v1/')->middleware('detect.platform')->group(function () {
    Route::middleware(['auth:api_customer', 'check.customer.account.status'])->group(function () {
        Route::post('orders/checkout', [PlaceOrderController::class, 'placeOrder']);
        Route::post('orders/create-stripe-session', [StripePaymentController::class, 'createCheckoutSession']); // // create checkout session (returns stripe checkout url)
        Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);  // stripe webhook (Stripe will call this)
    });
});
