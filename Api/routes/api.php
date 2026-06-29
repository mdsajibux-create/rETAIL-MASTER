<?php

use App\Http\Controllers\Api\V1\Com\FrontendPageSettingsController;
use App\Http\Controllers\Api\V1\Com\HeaderFooterController;
use App\Http\Controllers\Api\V1\Com\LiveLocationController;
use App\Http\Controllers\Api\V1\ContactManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\CustomerProductQueryController;
use App\Http\Controllers\Api\V1\Customer\PlaceOrderController;
use App\Http\Controllers\Api\V1\DeliveryChargeCalculateController;
use App\Http\Controllers\Api\V1\FrontendController;
use App\Http\Controllers\Api\V1\FrontendSettingsController;
use App\Http\Controllers\Api\V1\OtherChargeInfoController;
use App\Http\Controllers\Api\V1\StripePaymentController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\Customer\app\Http\Controllers\Api\V1\SubscriberManageController;
use Modules\Deliveryman\app\Http\Controllers\Api\V1\AdminDeliverymanManageController;
use Modules\RolePermission\app\Http\Controllers\Api\V1\PermissionController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\ComSiteGeneralController;

Route::group(['prefix' => 'v1/'], function () {

    Route::post('/token', [UserController::class, 'login']);
    Route::post('/refresh-token', [UserController::class, 'refreshToken']);
    Route::post('/forget-password', [UserController::class, 'forgetPassword']);
    Route::post('/reset-password', [UserController::class, 'resetPassword']);

    Route::group(['middleware' => ['auth:sanctum', ApiAuthMiddleware::class]], function () {
        // permission
        Route::get('/permissions', [PermissionController::class, 'permissions']);
        Route::get('/roles', [PermissionController::class, 'roles']);
        Route::get('me', [UserController::class, 'me']);

        Route::middleware('detect.platform')->group(function () {
            Route::post('/logout', [UserController::class, 'logout']);
            Route::group(['prefix' => 'user/'], function () {
                Route::get('me', [UserController::class, 'me']);
                Route::get('profile', [UserController::class, 'userProfile']);
                Route::post('/profile-edit', [UserController::class, 'userProfileUpdate']);
                Route::post('/email-change', [UserController::class, 'userEmailUpdate']);
                Route::patch('/change-password', [UserController::class, 'changePassword']);
            });
        });
    });

    Route::post('contact-us', [ContactManageController::class, 'store']);

    /*--------------------- Route without auth  ----------------------------*/
        Route::middleware('detect.platform')->group(function () {
            Route::group(['prefix' => 'auth/'], function () {
                Route::get('google', [UserController::class, 'redirectToGoogle']);
                Route::get('google/callback', [UserController::class, 'handleGoogleCallback']);
                Route::get('facebook', [UserController::class, 'redirectToFacebook']);
                Route::get('facebook/callback', [UserController::class, 'handleFacebookCallback']);
                Route::post('forget-password', [UserController::class, 'forgetPassword']);
                Route::post('verify-token', [UserController::class, 'verifyToken']);
                Route::post('reset-password', [UserController::class, 'resetPassword']);
            });

            // Product Category
            Route::group(['prefix' => 'product-category/'], function () {
                Route::get('list', [FrontendController::class, 'productCategoryList']);
                Route::get('product', [FrontendController::class, 'categoryWiseProducts']);
            });
        });

        // public routes for frontend
        Route::middleware('detect.platform')->group(function () {
            Route::get('/site-general-info', [ComSiteGeneralController::class, 'siteGeneralInfo']);
            Route::get('/settings', [FrontendSettingsController::class, 'settingsData']);
            Route::get('/language/{slug}', [FrontendSettingsController::class, 'languageData']);
            Route::get('/pages/{slug}', [FrontendSettingsController::class, 'singlePageDetails']);
            Route::get('/all/pages', [FrontendSettingsController::class, 'allPages']);
            Route::get('/footer', [HeaderFooterController::class, 'siteFooterInfo']);
            Route::get('/maintenance-page-settings', [ComSiteGeneralController::class, 'siteMaintenancePage']);
            Route::get('/google-map-settings', [ComSiteGeneralController::class, 'googleMapSettings']);
            Route::get('/product-types', [FrontendController::class, 'productTypes']);

            // product
            Route::get('/home-products', [FrontendController::class, 'homeProductData']);
            Route::post('/product-list', [FrontendController::class, 'products']);
            Route::get('/product/{product_slug}', [FrontendController::class, 'productDetails']);

            // branch
            Route::get('branch-list', [FrontendController::class, 'branchList']);
            Route::get('branches', [FrontendController::class, 'branches']);
            Route::get('/branch-details/{slug}', [FrontendController::class, 'branchDetails']);

            Route::get('/zone-list', [FrontendController::class, 'zones']);
            Route::get('/tag-list', [FrontendController::class, 'tags']);
            Route::get('/brand-list', [FrontendController::class, 'brands']);
            Route::get('/product/attribute-list', [FrontendController::class, 'productAttributes']);
            Route::get('/behaviour-list', [FrontendController::class, 'behaviourList']);
            Route::get('/unit-list', [FrontendController::class, 'units']);
            Route::get('/customer-list', [FrontendController::class, 'customers']);
            Route::get('/department-list', [FrontendController::class, 'departments']);
            Route::get('/product-suggestion', [FrontendController::class, 'searchSuggestions']);
            Route::get('/keyword-suggestion', [FrontendController::class, 'keywordSuggestions']);
            Route::get('/orders/refund-reason-list', [FrontendController::class, 'orderRefundReasons']);
            Route::get('/coupons', [FrontendController::class, 'coupons']);
            Route::get('/pages/{slug}', [FrontendController::class, 'page']);
            Route::get('/all/pages', [FrontendController::class, 'pages']);
            Route::get('/get-check-out-page-extra-info', [FrontendController::class, 'checkOutPageExtraInfo']);
            Route::put('/update-location', [LiveLocationController::class, 'updateLocation']);
            Route::post('/track-order-location', [LiveLocationController::class, 'trackOrder']);
            Route::get('/vehicle-types/list-dropdown', [AdminDeliverymanManageController::class, 'vehicleTypeDropdown']);
            Route::get('/product-query/search-question', [CustomerProductQueryController::class, 'searchQuestions']);

            Route::post('/subscribe', [SubscriberManageController::class, 'subscribe']);
            Route::post('/unsubscribe', [SubscriberManageController::class, 'unsubscribe']);

            // pages settings routes
            Route::get('/register-page-settings', [FrontendPageSettingsController::class, 'RegisterPageSettings']);
            Route::get('/login-page-settings', [FrontendPageSettingsController::class, 'LoginPageSettings']);

            // delivery charge calculate
            Route::get('/calculate-delivery-charge', [DeliveryChargeCalculateController::class, 'calculateDeliveryCharge']);
            Route::get('/other-charge-info', [OtherChargeInfoController::class, 'otherChargeInformation']);
            Route::post('/checkout-info', [OtherChargeInfoController::class, 'checkoutInfo']);
            Route::post('/check-coupon', [CustomerOrderController::class, 'checkCoupon']);

            // customer place order
            Route::group(['namespace' => 'Api\V1', 'middleware' => ['auth:api_customer', 'check.customer.account.status']], function () {
                Route::post('orders/checkout', [PlaceOrderController::class, 'placeOrder']);
                // create checkout session (returns stripe checkout url)
                Route::post('orders/create-stripe-session', [StripePaymentController::class, 'createCheckoutSession']);
                // stripe webhook (Stripe will call this)
                Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
            });
        });

});