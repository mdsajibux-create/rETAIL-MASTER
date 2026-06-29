<?php

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Com\FrontendPageSettingsController;
use App\Http\Controllers\Api\V1\Com\HeaderFooterController;
use App\Http\Controllers\Api\V1\Com\LiveLocationController;
use App\Http\Controllers\Api\V1\ContactManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerManageController;
use App\Http\Controllers\Api\V1\Customer\CustomerProductQueryController;
use App\Http\Controllers\Api\V1\DeliveryChargeCalculateController;
use App\Http\Controllers\Api\V1\FrontendController;
use App\Http\Controllers\Api\V1\MigrationController;
use App\Http\Controllers\Api\V1\OtherChargeInfoController;
use App\Http\Controllers\Api\V1\SeederController;
use App\Http\Controllers\Api\V1\Seller\SellerManageController;
use App\Http\Controllers\Api\V1\TaxInfoController;
use App\Http\Controllers\Customer\PlaceOrderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\Customer\app\Http\Controllers\Api\V1\SubscriberManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\ComSiteGeneralController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\MenuManageController;

/* Admin Login */
Route::post('/token', [UserController::class, 'token']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/forget-password', [UserController::class, 'forgetPassword']);
Route::post('/verify-forget-password-token', [UserController::class, 'verifyForgetPasswordToken']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
Route::post('/store/ownerreg', [UserController::class, 'StoreOwnerRegistration']);
/* Partner (Shop Owner/Shop Staff/Delivery-Man/FitterMan Login) Login */
Route::post('partner/login', [LoginController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum', ApiAuthMiddleware::class]], function () {
    Route::get('/getpermissions', [PermissionController::class, 'getpermissions']);
    Route::get('/get-roles', [PermissionController::class, 'getRoles']);
    Route::post('/logout', [UserController::class, 'logout']);
    // Routes for managing general user-related actions, such as profile information and other user account operations.
    Route::group(['prefix' => 'user/'], function () {
        Route::get('me', [UserController::class, 'me']);
        Route::get('profile', [UserController::class, 'userProfile']);
        Route::post('/profile-edit', [UserController::class, 'userProfileUpdate']);
        Route::post('/email-change', [UserController::class, 'userEmailUpdate']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
    });
});
Route::post('contact-us', [ContactManageController::class, 'store']);


/*--------------------- Route without auth  ----------------------------*/
Route::group(['prefix' => 'v1/'], function () {

    // For customer register and login
    Route::group(['prefix' => 'customer/'], function () {
        Route::post('registration', [CustomerManageController::class, 'register']);
        Route::post('login', [CustomerManageController::class, 'login']);
        Route::post('forget-password', [CustomerManageController::class, 'forgetPassword']);
        Route::post('verify-token', [CustomerManageController::class, 'verifyToken']);
        Route::post('reset-password', [CustomerManageController::class, 'resetPassword']);
    });

    Route::group(['prefix' => 'seller/'], function () {
        Route::post('registration', [UserController::class, 'StoreOwnerRegistration']);
        // password reset
        Route::post('forget-password', [SellerManageController::class, 'forgetPassword']);
        Route::post('verify-token', [SellerManageController::class, 'verifyToken']);
        Route::post('reset-password', [SellerManageController::class, 'resetPassword']);
    });

    Route::group(['prefix' => 'auth/'], function () {
        Route::get('google', [UserController::class, 'redirectToGoogle']);
        Route::get('google/callback', [UserController::class, 'handleGoogleCallback']);
        Route::get('facebook', [UserController::class, 'redirectToFacebook']);
        Route::get('facebook/callback', [UserController::class, 'handleFacebookCallback']);
        Route::get('social/response', [UserController::class, 'socialJsonResponse'])->name('social.response');
        Route::post('forget-password', [UserController::class, 'forgetPassword']);
        Route::post('verify-token', [UserController::class, 'verifyToken']);
        Route::post('reset-password', [UserController::class, 'resetPassword']);
    });
    // Product Category
    Route::group(['prefix' => 'product-category/'], function () {
        Route::get('list', [FrontendController::class, 'productCategoryList']);
        Route::get('product', [FrontendController::class, 'categoryWiseProducts']);
    });

    // public routes for frontend
    Route::post('migrate-refresh', [MigrationController::class, 'migrateRefresh']);
    Route::post('seed', [SeederController::class, 'runSeeder']);
    Route::get('/slider-list', [FrontendController::class, 'allSliders']);
    Route::get('/product-list', [FrontendController::class, 'productList']);
    Route::get('/product/{product_slug}', [FrontendController::class, 'productDetails']);
    Route::post('/new-arrivals', [FrontendController::class, 'getNewArrivals']);
    Route::post('/best-selling-products', [FrontendController::class, 'getBestSellingProduct']);
    Route::get('/featured-products', [FrontendController::class, 'getFeaturedProduct']);
    Route::get('/week-best-products', [FrontendController::class, 'getWeekBestProducts']);
    Route::get('/trending-products', [FrontendController::class, 'getTrendingProducts']);
    Route::get('/popular-products', [FrontendController::class, 'getPopularProducts']);
    Route::post('/top-deal-products', [FrontendController::class, 'getTopDeals']);
    Route::get('/top-rated-products', [FrontendController::class, 'getTopRatedProducts']);
    Route::get('/banner-list', [FrontendController::class, 'index']);
    Route::post('/subscribe', [SubscriberManageController::class, 'subscribe']);
    Route::post('/unsubscribe', [SubscriberManageController::class, 'unsubscribe']);
    Route::get('/country-list', [FrontendController::class, 'countriesList']);
    Route::get('/state-list', [FrontendController::class, 'statesList']);
    Route::get('/city-list', [FrontendController::class, 'citiesList']);
    Route::get('/areas', [FrontendController::class, 'areas']);
    Route::get('/area-list', [FrontendController::class, 'areaList']);
    Route::get('/tag-list', [FrontendController::class, 'tagList']);
    Route::get('/brand-list', [FrontendController::class, 'brandList']);
    Route::get('/product/attribute-list', [FrontendController::class, 'productAttributeList']);
    Route::get('/store-types', [FrontendController::class, 'storeTypeList']);
    Route::get('/behaviour-list', [FrontendController::class, 'behaviourList']);
    Route::get('/unit-list', [FrontendController::class, 'unitList']);
    Route::get('/customer-list', [FrontendController::class, 'customerList']);
    Route::get('/store-list', [FrontendController::class, 'getStores']);
    Route::get('/store-list-dropdown', [FrontendController::class, 'getStoresDropdown']);
    Route::get('/store-details/{slug}', [FrontendController::class, 'getStoreDetails']);
    Route::get('/department-list', [FrontendController::class, 'departmentList']);
    Route::get('/flash-deals', [FrontendController::class, 'flashDeals']);
    Route::get('/flash-deal-products', [FrontendController::class, 'flashDealProducts']);
    Route::get('/product-suggestion', [FrontendController::class, 'getSearchSuggestions']);
    Route::get('/keyword-suggestion', [FrontendController::class, 'getKeywordSuggestions']);
    Route::get('/orders/refund-reason-list', [FrontendController::class, 'allOrderRefundReason']);
    Route::get('/coupons', [FrontendController::class, 'couponList']);
    Route::get('/blogs', [FrontendController::class, 'blogs']);
    Route::get('/blog/{slug}', [FrontendController::class, 'blogDetails']);
    Route::get('/become-a-seller', [FrontendController::class, 'becomeSeller']);
    Route::get('/about-us', [FrontendController::class, 'aboutUs']);
    Route::get('/contact-us', [FrontendController::class, 'contactUs']);
    Route::get('/pages/{slug}', [FrontendController::class, 'getPage']);
    Route::get('/all/pages', [FrontendController::class, 'allPage']);
    Route::get('/store-wise-products', [FrontendController::class, 'getStoreWiseProducts']);
    Route::get('/get-check-out-page-extra-info', [FrontendController::class, 'getCheckOutPageExtraInfo']);
    Route::get('/menu', [MenuManageController::class, 'index']);
    Route::post('/update-location',[LiveLocationController::class, 'update']);
    Route::post('/track-order-location',[LiveLocationController::class, 'trackOrder']);

    Route::get('/product-query/search-question', [CustomerProductQueryController::class, 'searchQuestion']);

    // home page footer api route
    Route::get('/footer', [HeaderFooterController::class, 'siteFooterInfo']);
    Route::get('/site-general-info', [ComSiteGeneralController::class, 'siteGeneralInfo']);
    Route::get('/maintenance-page-settings', [ComSiteGeneralController::class, 'siteMaintenancePage']);
    Route::get('/google-map-settings', [ComSiteGeneralController::class, 'googleMapSettings']);
    Route::get('/gdpr-cookie-settings', [ComSiteGeneralController::class, 'gdprCookieSettings']);

    // pages settings routes
    Route::get('/about-page-settings', [FrontendPageSettingsController::class, 'AboutPageSettings']);
    Route::get('/contact-page-settings', [FrontendPageSettingsController::class, 'ContactPageSettings']);
    Route::get('/register-page-settings', [FrontendPageSettingsController::class, 'RegisterPageSettings']);
    Route::get('/login-page-settings', [FrontendPageSettingsController::class, 'LoginPageSettings']);
    Route::get('/blog-page-settings', [FrontendPageSettingsController::class, 'BlogPageSettings']);
    Route::get('/product-details-page-settings', [FrontendPageSettingsController::class, 'productDetailsPageSettings']);

    // delivery charge calculate
    Route::post('/calculate-delivery-charge', [DeliveryChargeCalculateController::class, 'calculateDeliveryCharge']);
    Route::post('/store-tax-info', [TaxInfoController::class, 'storeTaxInformation']);
    Route::get('/other-charge-info', [OtherChargeInfoController::class, 'otherChargeInformation']);
    Route::post('/checkout-info', [OtherChargeInfoController::class, 'getCheckoutInfo']);

    // customer place order
    Route::group(['namespace' => 'Api\V1', 'middleware' => ['auth:api_customer', 'check.customer.account.status']], function () {
        Route::post('orders/checkout', [PlaceOrderController::class, 'placeOrder']);
    });

});
