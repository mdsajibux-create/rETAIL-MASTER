<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\Admin\AboutSettingsManageController;
use App\Http\Controllers\Api\V1\Admin\AdminAreaSetupManageController;
use App\Http\Controllers\Api\V1\Admin\AdminBannerManageController;
use App\Http\Controllers\Api\V1\Admin\AdminBlogManageController;
use App\Http\Controllers\Api\V1\Admin\AdminCashCollectionController;
use App\Http\Controllers\Api\V1\Admin\AdminCommissionManageController;
use App\Http\Controllers\Api\V1\Admin\AdminContactManageController;
use App\Http\Controllers\Api\V1\Admin\AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\AdminDeliverymanReviewManageController;
use App\Http\Controllers\Api\v1\Admin\AdminPosSalesController;
use App\Http\Controllers\Api\V1\Admin\AdminSellerManageController;
use App\Http\Controllers\Api\V1\Admin\AdminStoreNoticeController;
use App\Http\Controllers\Api\V1\Admin\AdminStoreTypeManageController;
use App\Http\Controllers\Api\V1\Admin\AdminSupportTicketManageController;
use App\Http\Controllers\Api\V1\Admin\BecomeSellerSettingsController;
use App\Http\Controllers\Api\V1\Admin\ContactSettingsManageController;
use App\Http\Controllers\Api\V1\Admin\LocationManageController;
use App\Http\Controllers\Api\V1\Com\AreaController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationManageController;
use App\Http\Controllers\Api\V1\Product\ProductAuthorController;
use App\Http\Controllers\Api\V1\SliderManageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductBrandController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Modules\Analytics\app\Http\Controllers\Api\V1\AdminReportAnalyticsManageController;
use Modules\Branch\app\Http\Controllers\Api\V1\AdminBranchManageController;
use Modules\Catalog\app\Http\Controllers\Api\V1\AdminUnitManageController;
use Modules\Catalog\app\Http\Controllers\Api\V1\ProductAttributeController;
use Modules\Catalog\app\Http\Controllers\Api\V1\TagManageController;
use Modules\Coupon\app\Http\Controllers\Api\V1\CouponManageController;
use Modules\Customer\app\Http\Controllers\Api\V1\CustomerManageController as AdminCustomerManageController;
use Modules\Customer\app\Http\Controllers\Api\V1\SubscriberManageController;
use Modules\Deliveryman\app\Http\Controllers\Api\V1\AdminDeliverymanManageController;
use Modules\Feedback\app\Http\Controllers\Api\V1\AdminProductQueryManageController;
use Modules\Feedback\app\Http\Controllers\Api\V1\AdminReviewManageController;
use Modules\Order\app\Http\Controllers\Api\V1\AdminOrderManageController;
use Modules\Order\app\Http\Controllers\Api\V1\AdminOrderRefundManageController;
use Modules\Product\app\Http\Controllers\Api\V1\AdminInventoryManageController;
use Modules\Product\app\Http\Controllers\Api\V1\AdminProductManageController;
use Modules\Promotion\app\Http\Controllers\Api\V1\AdminFlashSaleManageController;
use Modules\SupportTicket\Http\Controllers\Api\V1\DepartmentManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\EmailSettingsController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\EmailTemplateManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\MenuManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\PageSettingsManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\PagesManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\SystemManagementController;
use Modules\Wallet\app\Http\Controllers\Api\AdminWithdrawGatewayManageController;
use Modules\Wallet\App\Http\Controllers\Api\AdminWithdrawManageController;
use Modules\Wallet\app\Http\Controllers\Api\AdminWithdrawRequestManageController;
use Modules\Wallet\App\Http\Controllers\Api\AdminWithdrawSettingsController;
use Modules\Wallet\app\Http\Controllers\Api\WalletManageAdminController;


Route::group(['namespace' => 'Api\V1', 'middleware' => ['auth:sanctum']], function () {
    /*--------------------- Com route start  ----------------------------*/
    Route::get('/logout', [UserController::class, 'logout']);
    // media manage
    Route::group(['prefix' => 'media-upload'], function () {
        Route::post('/store', [MediaController::class, 'mediaUpload']);
        Route::get('/load-more', [MediaController::class, 'load_more']);
        Route::post('/alt', [MediaController::class, 'alt_change']);
        Route::post('/delete', [MediaController::class, 'delete_media']);
    });
    // Marketing area manage
    Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_AREA_LIST->value]], function () {
        Route::get('com/area/list', [AreaController::class, 'index']);
    });
    Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_AREA_ADD->value]], function () {
        Route::get('com/area/{id}', [AreaController::class, 'show']);
        Route::post('com/area/add', [AreaController::class, 'store']);
        Route::post('com/area/update', [AreaController::class, 'update']);
        Route::put('com/area/status/{id}', [AreaController::class, 'changeStatus']);
        Route::delete('com/area/remove/{id}', [AreaController::class, 'destroy']);
    });
    /*--------------------- Com route end  ----------------------------*/

    /* --------------------- Admin route start ------------------------- */
    Route::group(['prefix' => 'admin/'], function () {
        // Dashboard manage
        Route::group(['prefix' => 'dashboard/', 'middleware' => ['permission:' . PermissionKey::ADMIN_DASHBOARD->value]], function () {
            Route::get('/', [AdminDashboardController::class, 'summaryData']);
            Route::get('sales-summary', [AdminDashboardController::class, 'salesSummaryData']);
            Route::get('other-summary', [AdminDashboardController::class, 'otherSummaryData']);
            Route::get('order-growth-summary', [AdminDashboardController::class, 'orderGrowthData']);
        });
        // POS Manage
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_POS_SALES->value]], function () {
            Route::group(['prefix' => 'pos/'], function () {
                Route::get('', [AdminPosSalesController::class, 'index']); // Show POS dashboard
                Route::post('process', [AdminPosSalesController::class, 'processSale']); // Process a sale
                Route::get('products', [AdminPosSalesController::class, 'fetchProducts']); // Fetch products for POS
                Route::post('add-to-cart', [AdminPosSalesController::class, 'addToCart']); // Add product to POS cart
                Route::get('cart', [AdminPosSalesController::class, 'getCart']); // Fetch current POS cart
                Route::post('remove-from-cart', [AdminPosSalesController::class, 'removeFromCart']); // Remove item from POS cart
                Route::post('apply-discount', [AdminPosSalesController::class, 'applyDiscount']); // Apply discount to the order
                Route::post('apply-tax', [AdminPosSalesController::class, 'applyTax']); // Apply tax to the order
                Route::get('customers', [AdminPosSalesController::class, 'fetchCustomers']); // Fetch customers for POS
                Route::post('add-customer', [AdminPosSalesController::class, 'addCustomer']); // Add a new customer
                Route::post('finalize-sale', [AdminPosSalesController::class, 'finalizeSale']); // Finalize the sale and generate invoice
                Route::get('order-history', [AdminPosSalesController::class, 'orderHistory']); // View POS order history
                // POS Settings (with specific permission)
                Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_POS_SETTINGS->value]], function () {
                    Route::get('settings', [AdminPosSalesController::class, 'posSettings']); // POS settings
                });
            });
        });


        // Orders & Reviews Manage
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_ORDERS_ALL->value]], function () {
            Route::group(['prefix' => 'orders/'], function () {
                Route::get('invoice', [AdminOrderManageController::class, 'invoice']);
                Route::post('change-order-status', [AdminOrderManageController::class, 'changeOrderStatus']);
                Route::post('change-payment-status', [AdminOrderManageController::class, 'changePaymentStatus']);
                Route::post('assign-deliveryman', [AdminOrderManageController::class, 'assignDeliveryMan']);
                Route::post('cancel-order', [AdminOrderManageController::class, 'cancelOrder']);
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


        // Product manage
        Route::group(['prefix' => 'product/'], function () {
            // Product Inventory
            Route::group(['prefix' => 'inventory', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_INVENTORY->value]], function () {
                Route::get('/', [AdminInventoryManageController::class, 'allInventories']);
                Route::post('update', [AdminInventoryManageController::class, 'updateInventory']);
                Route::post('remove', [AdminInventoryManageController::class, 'deleteInventory']);
            });
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCTS_MANAGE->value]], function () {
                Route::get('list', [AdminProductManageController::class, 'index']);
                Route::post('add', [AdminProductManageController::class, 'store']);
                Route::post('add-to-featured', [AdminProductManageController::class, 'addToFeatured']);
                Route::get('details/{slug}', [AdminProductManageController::class, 'show']);
                Route::post('update', [AdminProductManageController::class, 'update']);
                Route::delete('remove/{id?}', [AdminProductManageController::class, 'destroy']);
                Route::post('approve', [AdminProductManageController::class, 'approveProductRequests']);
                Route::get('request', [AdminProductManageController::class, 'productRequests'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCT_PRODUCT_APPROVAL_REQ->value);
                Route::post('export', [AdminProductManageController::class, 'export'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCT_PRODUCT_BULK_EXPORT->value);
                Route::post('import', [AdminProductManageController::class, 'import'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCT_PRODUCT_BULK_IMPORT->value);
                Route::post('change-status', [AdminProductManageController::class, 'changeStatus']);
                Route::get('stock-report', [AdminProductManageController::class, 'lowOrOutOfStockProducts'])->middleware('permission:' . PermissionKey::ADMIN_PRODUCT_STOCK_REPORT->value);
            });
        });
        // seller Store Management
        Route::group(['prefix' => 'store/'], function () {
            // Store List Routes
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_STORE_LIST->value]], function () {
                Route::get('list', [AdminBranchManageController::class, 'index']);
                Route::get('seller-stores', [AdminBranchManageController::class, 'sellerStores']);
                Route::get('details/{id}', [AdminBranchManageController::class, 'show']);
            });
            // Store Add Routes
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_STORE_ADD->value]], function () {
                Route::post('add', [AdminBranchManageController::class, 'store']);
                Route::post('update', [AdminBranchManageController::class, 'update']);
                Route::post('change-status', [AdminBranchManageController::class, 'changeStatus']);
                Route::delete('remove/{id}', [AdminBranchManageController::class, 'destroy']);
                Route::get('deleted-records', [AdminBranchManageController::class, 'deletedRecords']);
            });
            // Store Approval Request Routes
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_STORE_APPROVAL->value]], function () {
                Route::get('request', [AdminBranchManageController::class, 'storeRequest']);
                Route::post('approve', [AdminBranchManageController::class, 'approveStoreRequests']);
            });
            // Recommended Store Routes
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_STORE_RECOMMENDED->value]], function () {
                Route::get('recommended', [AdminBranchManageController::class, 'recommendedStores']);
                Route::post('set-recommended', [AdminBranchManageController::class, 'setRecommended']);
            });
        });
        // Flash Sale manage
        Route::group(['prefix' => 'promotional/'], function () {
            // Flash Deals
            Route::group(['prefix' => 'flash-deals'], function () {
                Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PROMOTIONAL_FLASH_SALE_MANAGE->value]], function () {
                    Route::get('list', [AdminFlashSaleManageController::class, 'getFlashSale']);
                    Route::get('list-dropdown', [AdminFlashSaleManageController::class, 'flashSaleDropdown']);
                    Route::post('add', [AdminFlashSaleManageController::class, 'createFlashSale']);
                    Route::post('add-products', [AdminFlashSaleManageController::class, 'adminAddProductToFlashSale']);
                    Route::get('all-products', [AdminFlashSaleManageController::class, 'getAllFlashSaleProducts']);
                    Route::get('store-wise-products', [AdminFlashSaleManageController::class, 'getProductsNotInFlashSale']);
                    Route::post('update-products', [AdminFlashSaleManageController::class, 'adminUpdateProductToFlashSale']);
                    Route::get('details/{id}', [AdminFlashSaleManageController::class, 'FlashSaleDetails']);
                    Route::post('update', [AdminFlashSaleManageController::class, 'updateFlashSale']);
                    Route::post('change-status', [AdminFlashSaleManageController::class, 'changeStatus']);
                    Route::delete('remove/{id}', [AdminFlashSaleManageController::class, 'deleteFlashSale']);
                    Route::post('deactivate', [AdminFlashSaleManageController::class, 'deactivateFlashSale']);
                });
                // Join Deals
                Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PROMOTIONAL_FLASH_SALE_JOIN_DEALS->value]], function () {
                    Route::get('join-request', [AdminFlashSaleManageController::class, 'flashSaleProductRequest']);
                    Route::post('join-request/approve', [AdminFlashSaleManageController::class, 'approveFlashSaleProducts']);
                    Route::post('join-request/reject', [AdminFlashSaleManageController::class, 'rejectFlashSaleProducts']);
                });
            });
            // Banner Management
            Route::group(['prefix' => 'banner/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PROMOTIONAL_BANNER_MANAGE->value]], function () {
                Route::get('list', [AdminBannerManageController::class, 'index']);
                Route::post('add', [AdminBannerManageController::class, 'store']);
                Route::get('details/{id}', [AdminBannerManageController::class, 'show']);
                Route::post('update', [AdminBannerManageController::class, 'update']);
                Route::post('change-status', [AdminBannerManageController::class, 'changeStatus']);
                Route::delete('remove/{id}', [AdminBannerManageController::class, 'destroy']);
            });
        });
        // Customer Manage
        Route::group(['prefix' => 'customer/'], function () {
            // CUSTOMER
            Route::group(['permission:' . PermissionKey::ADMIN_CUSTOMER_MANAGEMENT_LIST->value], function () {
                Route::get('list', [AdminCustomerManageController::class, 'getCustomerList']);
                Route::get('details/{id}', [AdminCustomerManageController::class, 'getCustomerDetails']);
                Route::post('register', [AdminCustomerManageController::class, 'register']);
                Route::post('change-status', [AdminCustomerManageController::class, 'changeStatus']);
                Route::post('change-password', [AdminCustomerManageController::class, 'changePassword']);
                Route::post('email-verify', [AdminCustomerManageController::class, 'emailVerify']);
                Route::post('update-profile', [AdminCustomerManageController::class, 'updateProfile']);
                Route::post('suspend', [AdminCustomerManageController::class, 'suspend']);
                Route::delete('remove/{customer_id}', [AdminCustomerManageController::class, 'destroy']);
            });
            // Newsletter
            Route::group(['permission:' . PermissionKey::ADMIN_CUSTOMER_MANAGEMENT_LIST->value], function () {
                Route::group(['prefix' => 'newsletter/'], function () {
                    Route::get('list', [SubscriberManageController::class, 'allSubscribers']);
                    Route::post('bulk-status-change', [SubscriberManageController::class, 'bulkStatusChange']);
                    Route::post('bulk-email-send', [SubscriberManageController::class, 'sendBulkEmail']);
                    Route::delete('remove/{id}', [SubscriberManageController::class, 'destroy']);
                });
            });
        });
        // contact message
        Route::group(['prefix' => 'contact-messages/', 'middleware' => ['permission:' . PermissionKey::ADMIN_CUSTOMER_CONTACT_MESSAGES->value]], function () {
            Route::get('list', [AdminContactManageController::class, 'index']);
            Route::post('reply', [AdminContactManageController::class, 'reply']);
            Route::post('change-status', [AdminContactManageController::class, 'changeStatus']);
            Route::post('remove', [AdminContactManageController::class, 'destroy']);
        });
        // Seller Manage
        Route::group(['prefix' => 'seller/', 'middleware' => ['permission:' . PermissionKey::ADMIN_SELLER_MANAGEMENT->value]], function () {
            Route::post('registration', [UserController::class, 'StoreOwnerRegistration'])->middleware('permission:' . PermissionKey::ADMIN_SELLER_REGISTRATION->value);
            Route::post('update', [AdminSellerManageController::class, 'updateProfile']);
            Route::get('list', [AdminSellerManageController::class, 'getSellerList']);
            Route::get('history/{id}', [AdminSellerManageController::class, 'sellerDashboard']);
            Route::get('active', [AdminSellerManageController::class, 'getActiveSellerList']);
            Route::get('details/{id}', [AdminSellerManageController::class, 'getSellerDetails']);
            Route::get('list/pending', [AdminSellerManageController::class, 'pendingSellers']);
            Route::post('approve', [AdminSellerManageController::class, 'approveSeller']);
            Route::post('suspend', [AdminSellerManageController::class, 'rejectSeller']);
            Route::post('change-status', [AdminSellerManageController::class, 'changeStatus']);
            Route::post('change-password', [AdminSellerManageController::class, 'changePassword']);
            Route::delete('remove/{seller_id}', [AdminSellerManageController::class, 'destroy']);
        });
        // Department manage
        Route::group(['prefix' => 'department/'], function () {
            Route::get('list', [DepartmentManageController::class, 'index']);
            Route::post('add', [DepartmentManageController::class, 'store']);
            Route::get('details/{id}', [DepartmentManageController::class, 'show']);
            Route::post('update', [DepartmentManageController::class, 'update']);
            Route::delete('remove/{id}', [DepartmentManageController::class, 'destroy']);
        });
        // Location Manage
        Route::group(['prefix' => 'location/'], function () {
            // Country
            Route::group(['prefix' => 'country/'], function () {
                Route::group(['middleware' => ['permission:' . PermissionKey::PRODUCT_ATTRIBUTE_ADD->value]], function () {
                    Route::get('list', [LocationManageController::class, 'countriesList']);
                    Route::post('add', [LocationManageController::class, 'storeCountry']);
                    Route::get('details', [LocationManageController::class, 'countryDetails']);
                    Route::post('update', [LocationManageController::class, 'updateCountry']);
                    Route::delete('remove/{id}', [LocationManageController::class, 'destroyCountry']);
                });
            });
            // State
            Route::group(['prefix' => 'state/'], function () {
                Route::group(['middleware' => ['permission:' . PermissionKey::PRODUCT_ATTRIBUTE_ADD->value]], function () {
                    Route::get('list', [LocationManageController::class, 'statesList']);
                    Route::post('add', [LocationManageController::class, 'storeState']);
                    Route::get('details', [LocationManageController::class, 'stateDetails']);
                    Route::post('update', [LocationManageController::class, 'updateState']);
                    Route::delete('remove/{id}', [LocationManageController::class, 'destroyState']);
                });
            });
            // City
            Route::group(['prefix' => 'city/'], function () {
                Route::group(['middleware' => ['permission:' . PermissionKey::PRODUCT_ATTRIBUTE_ADD->value]], function () {
                    Route::get('list', [LocationManageController::class, 'citiesList']);
                    Route::post('add', [LocationManageController::class, 'storeCity']);
                    Route::get('details', [LocationManageController::class, 'cityDetails']);
                    Route::post('update', [LocationManageController::class, 'updateCity']);
                    Route::delete('remove/{id}', [LocationManageController::class, 'destroyCity']);
                });
            });
            // Area
            Route::group(['prefix' => 'area/'], function () {
                Route::group(['middleware' => ['permission:' . PermissionKey::PRODUCT_ATTRIBUTE_ADD->value]], function () {
                    Route::get('list', [LocationManageController::class, 'areasList']);
                    Route::post('add', [LocationManageController::class, 'storeArea']);
                    Route::get('details', [LocationManageController::class, 'areaDetails']);
                    Route::post('update', [LocationManageController::class, 'updateArea']);
                    Route::delete('remove/{id}', [LocationManageController::class, 'destroyArea']);
                });
            });
        });

        // Slider manage
        Route::group(['prefix' => 'slider/', 'middleware' => ['permission:' . PermissionKey::ADMIN_SLIDER_MANAGE_LIST->value]], function () {
            Route::get('list', [SliderManageController::class, 'index']);
            Route::post('add', [SliderManageController::class, 'store']);
            Route::get('details/{id}', [SliderManageController::class, 'show']);
            Route::post('update', [SliderManageController::class, 'update']);
            Route::post('change-status', [SliderManageController::class, 'changeStatus']);
            Route::delete('remove/{id}', [SliderManageController::class, 'destroy']);
        });

        // media manage
        Route::group(['prefix' => 'media-manage/', 'middleware' => ['permission:' . PermissionKey::ADMIN_MEDIA_MANAGE->value]], function () {
            Route::get('/', [MediaController::class, 'allMediaManage']);
            Route::delete('delete/{id?}', [MediaController::class, 'mediaFileDelete']);
        });

        // Product Brand Routing
        Route::group(['prefix' => 'brand/'], function () {
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_BRAND_LIST->value]], function () {
                Route::get('list', [ProductBrandController::class, 'index']);
                Route::post('add', [ProductBrandController::class, 'store']);
                Route::post('update', [ProductBrandController::class, 'update']);
                Route::get('details/{id}', [ProductBrandController::class, 'show']);
                Route::post('change-status', [ProductBrandController::class, 'productBrandStatus']);
                Route::delete('remove/{id}', [ProductBrandController::class, 'destroy']);
            });
        });
        // Product Author manage
        Route::group(['prefix' => 'product/author/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_AUTHORS_MANAGE->value]], function () {
            Route::get('list', [ProductAuthorController::class, 'index']);
            Route::post('add', [ProductAuthorController::class, 'store']);
            Route::get('details/{id}', [ProductAuthorController::class, 'show']);
            Route::post('update', [ProductAuthorController::class, 'update']);
            Route::delete('remove/{id}', [ProductAuthorController::class, 'destroy']);
            Route::post('change-status', [ProductAuthorController::class, 'changeStatus']);
            Route::post('approve', [ProductAuthorController::class, 'approveAuthors']);
            Route::get('author-request', [ProductAuthorController::class, 'authorRequests']);
        });
        // Product Category Routing
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_CATEGORY_LIST->value]], function () {
            Route::get('product-categories/list', [ProductCategoryController::class, 'index']);
            Route::post('product-categories/add', [ProductCategoryController::class, 'store']);
            Route::get('product-categories/details/{id}', [ProductCategoryController::class, 'show']);
            Route::post('product-categories/update', [ProductCategoryController::class, 'store']);
            Route::post('product-categories/change-status', [ProductCategoryController::class, 'productCategoryStatus']);
            Route::delete('product-categories/remove/{id}', [ProductCategoryController::class, 'destroy']);
        });
        // User Management
        Route::group(['middleware' => [getPermissionMiddleware('ban-user')]], function () {
            Route::post('users/block-user', [UserController::class, 'banUser']);
        });
        Route::group(['middleware' => [getPermissionMiddleware('active-user')]], function () {
            Route::post('users/unblock-user', [UserController::class, 'activeUser']);
        });
        //Product Attribute Management
        Route::group(['prefix' => 'attribute/', 'middleware/' => ['permission:' . PermissionKey::PRODUCT_ATTRIBUTE_ADD->value]], function () {
            Route::get('list', [ProductAttributeController::class, 'index']);
            Route::get('details/{id}', [ProductAttributeController::class, 'show']);
            Route::get('type-wise', [ProductAttributeController::class, 'typeWiseAttributes']);
            Route::post('add', [ProductAttributeController::class, 'store']);
            Route::post('update', [ProductAttributeController::class, 'update']);
            Route::post('change-status', [ProductAttributeController::class, 'changeStatus']);
            Route::delete('remove/{id}', [ProductAttributeController::class, 'destroy']);
        });
        // Coupon manage
        Route::group(['prefix' => 'coupon/', 'middleware' => ['permission:' . PermissionKey::ADMIN_COUPON_MANAGE->value]], function () {
            Route::get('list', [CouponManageController::class, 'index']);
            Route::get('coupon-wise-line', [CouponManageController::class, 'couponWiseLine']);
            Route::get('details/{id}', [CouponManageController::class, 'show']);
            Route::post('add', [CouponManageController::class, 'store']);
            Route::post('update', [CouponManageController::class, 'update']);
            Route::post('status-change', [CouponManageController::class, 'changeStatus']);
            Route::delete('remove/{id}', [CouponManageController::class, 'destroy']);
        });
        Route::group(['prefix' => 'coupon-line/', 'middleware' => ['permission:' . PermissionKey::ADMIN_COUPON_LINE_MANAGE->value]], function () {
            Route::get('list', [CouponManageController::class, 'couponLineIndex']);
            Route::get('details/{id}', [CouponManageController::class, 'couponLineShow']);
            Route::post('add', [CouponManageController::class, 'couponLineStore']);
            Route::post('update', [CouponManageController::class, 'couponLineUpdate']);
            Route::delete('remove/{id}', [CouponManageController::class, 'couponLineDestroy']);
        });
        // Tag manage
        Route::group(['prefix' => 'tag/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_TAG_LIST->value]], function () {
            Route::get('list', [TagManageController::class, 'index']);
            Route::post('add', [TagManageController::class, 'store']);
            Route::get('details/{id}', [TagManageController::class, 'show']);
            Route::post('update', [TagManageController::class, 'update']);
            Route::delete('remove/{id}', [TagManageController::class, 'destroy']);
        });
        // Unit manage
        Route::group(['prefix' => 'unit/', 'middleware' => ['permission:' . PermissionKey::ADMIN_PRODUCT_UNIT_LIST->value]], function () {
            Route::get('list', [AdminUnitManageController::class, 'index']);
            Route::post('add', [AdminUnitManageController::class, 'store']);
            Route::get('details/{id}', [AdminUnitManageController::class, 'show']);
            Route::post('update', [AdminUnitManageController::class, 'update']);
            Route::delete('remove/{id}', [AdminUnitManageController::class, 'destroy']);
        });
        // Blog manage
        Route::group(['prefix' => 'blog/', 'middleware' => ['permission:' . PermissionKey::ADMIN_BLOG_MANAGE->value]], function () {
            Route::get('list', [AdminBlogManageController::class, 'blogIndex']);
            Route::post('add', [AdminBlogManageController::class, 'blogStore']);
            Route::get('details/{id}', [AdminBlogManageController::class, 'blogShow']);
            Route::post('update', [AdminBlogManageController::class, 'blogUpdate']);
            Route::post('change-status', [AdminBlogManageController::class, 'changeStatus']);
            Route::delete('remove/{id}', [AdminBlogManageController::class, 'blogDestroy']);
            // Blog category manage
            Route::group(['prefix' => 'category/', 'middleware' => ['permission:' . PermissionKey::ADMIN_BLOG_CATEGORY_MANAGE->value]], function () {
                Route::get('list', [AdminBlogManageController::class, 'blogCategoryIndex']);
                Route::get('fetch/list', [AdminBlogManageController::class, 'blogCategoryList']);
                Route::post('add', [AdminBlogManageController::class, 'blogCategoryStore']);
                Route::get('details/{id}', [AdminBlogManageController::class, 'blogCategoryShow']);
                Route::post('update', [AdminBlogManageController::class, 'blogCategoryUpdate']);
                Route::post('change-status', [AdminBlogManageController::class, 'categoryStatusChange']);
                Route::delete('remove/{id}', [AdminBlogManageController::class, 'blogCategoryDestroy']);
            });
        });

        // Staff manage
        Route::group(['prefix' => 'staff/'], function () {
            Route::get('list', [StaffController::class, 'index'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_LIST->value]);
            Route::post('add', [StaffController::class, 'store'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
            Route::get('details/{id}', [StaffController::class, 'show'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
            Route::post('update', [StaffController::class, 'update'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
            Route::post('change-status', [StaffController::class, 'changeStatus'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
            Route::post('change-password', [StaffController::class, 'changePassword'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
            Route::delete('remove/{id}', [StaffController::class, 'destroy'])->middleware(['permission:' . PermissionKey::ADMIN_STAFF_MANAGE->value]);
        });

        // Pages manage
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PAGES_LIST->value]], function () {
            Route::get('pages/list', [PagesManageController::class, 'pagesIndex']);
            Route::post('pages/store', [PagesManageController::class, 'pagesStore']);
            Route::get('pages/details/{id}', [PagesManageController::class, 'pagesShow']);
            Route::post('pages/update', [PagesManageController::class, 'pagesUpdate']);
            Route::post('pages/status-change', [PagesManageController::class, 'pagesStatusChange']);
            Route::delete('pages/remove/{id}', [PagesManageController::class, 'pagesDestroy']);
        });

        // Notifications manage
        Route::prefix('notifications/')->middleware(['permission:' . PermissionKey::ADMIN_NOTIFICATION_MANAGEMENT->value])->group(function () {
            Route::get('/', [NotificationManageController::class, 'index']);
            Route::post('/read', [NotificationManageController::class, 'markAsRead']);
            Route::delete('remove/{id}', [NotificationManageController::class, 'destroy']);
        });

        // Store Notice manage
        Route::prefix('store-notices/')->middleware(['permission:' . PermissionKey::ADMIN_NOTICE_MANAGEMENT->value])->group(function () {
            Route::get('list', [AdminStoreNoticeController::class, 'index']);
            Route::post('add', [AdminStoreNoticeController::class, 'store']);
            Route::get('details/{id}', [AdminStoreNoticeController::class, 'show']);
            Route::post('update', [AdminStoreNoticeController::class, 'update']);
            Route::post('change-status', [AdminStoreNoticeController::class, 'changeStatus']);
            Route::delete('remove/{id}', [AdminStoreNoticeController::class, 'destroy']);
        });

        Route::group(['prefix' => 'feedback-control/'], function () {
            Route::group(['prefix' => 'review/'], function () {
                Route::get('/', [AdminReviewManageController::class, 'index']);
                Route::post('approve', [AdminReviewManageController::class, 'approveReview']);
                Route::post('reject', [AdminReviewManageController::class, 'rejectReview']);
                Route::post('remove', [AdminReviewManageController::class, 'destroy']);
            });
            Route::group(['prefix' => 'questions/'], function () {
                Route::get('/', [AdminProductQueryManageController::class, 'getAllQueries']);
                Route::post('change-status', [AdminProductQueryManageController::class, 'changeStatus']);
                Route::post('remove', [AdminProductQueryManageController::class, 'destroy']);
            });
        });
        // Admin Deliveryman management
        Route::prefix('deliveryman/')->group(function () {
            // delivery man manage
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_DELIVERYMAN_MANAGE_LIST->value]], function () {
                Route::get('list', [AdminDeliverymanManageController::class, 'index']);
                Route::get('list-dropdown', [AdminDeliverymanManageController::class, 'deliverymanDropdownList']);
                Route::get('request', [AdminDeliverymanManageController::class, 'deliverymanRequest']);
                Route::post('add', [AdminDeliverymanManageController::class, 'store']);
                Route::post('change-password', [AdminDeliverymanManageController::class, 'changePassword']);
                Route::get('details/{id}', [AdminDeliverymanManageController::class, 'show']);
                Route::post('update', [AdminDeliverymanManageController::class, 'update']);
                Route::post('change-status', [AdminDeliverymanManageController::class, 'changeStatus']);
                Route::post('approve', [AdminDeliverymanManageController::class, 'approveRequest']);
                Route::delete('remove/{id}', [AdminDeliverymanManageController::class, 'destroy']);
                Route::get('history/{id}', [AdminDeliverymanManageController::class, 'deliverymanDashboard']);
            });
            //vehicle-types
            Route::prefix('vehicle-types/')->middleware(['permission:' . PermissionKey::ADMIN_DELIVERYMAN_VEHICLE_TYPE->value])->group(function () {
                Route::get('list', [AdminDeliverymanManageController::class, 'indexVehicle']);
                Route::get('list-dropdown', [AdminDeliverymanManageController::class, 'vehicleTypeDropdown']);
                Route::get('request', [AdminDeliverymanManageController::class, 'vehicleRequest']);
                Route::post('add', [AdminDeliverymanManageController::class, 'storeVehicle']);
                Route::get('details/{id}', [AdminDeliverymanManageController::class, 'showVehicle']);
                Route::post('update', [AdminDeliverymanManageController::class, 'updateVehicle']);
                Route::post('change-status', [AdminDeliverymanManageController::class, 'changeVehicleStatus']);
                Route::post('approve', [AdminDeliverymanManageController::class, 'approveVehicleRequest']);
                Route::delete('remove/{id}', [AdminDeliverymanManageController::class, 'destroyVehicle']);
            });
            // deliveryman review manage
            Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_DELIVERYMAN_MANAGE_REVIEW->value]], function () {
                Route::get('reviews', [AdminDeliverymanReviewManageController::class, 'index']);
            });
        });

        // Support ticket management
        Route::group(['prefix' => 'support-ticket/', 'middleware' => 'permission:' . PermissionKey::ADMIN_SUPPORT_TICKETS_MANAGE->value], function () {
            Route::get('list', [AdminSupportTicketManageController::class, 'index']);
            Route::get('details/{id?}', [AdminSupportTicketManageController::class, 'show']);
            Route::post('change-priority-status', [AdminSupportTicketManageController::class, 'changePriorityStatus']);
            Route::post('resolve', [AdminSupportTicketManageController::class, 'resolve']);
            Route::post('message/reply', [AdminSupportTicketManageController::class, 'replyMessage']);
            Route::get('get-ticket-messages/{ticket_id}', [AdminSupportTicketManageController::class, 'getTicketMessages']);
            Route::delete('remove/{ticket_id}', [AdminSupportTicketManageController::class, 'destroy']);
        });

        // FINANCIAL WITHDRAWALS management
        Route::group(['prefix' => 'financial/'], function () {
            // waller manage
            Route::group(['prefix' => 'wallet/', PermissionKey::ADMIN_WALLET_MANAGE->value], function () {
                Route::match(['get', 'post'], 'settings', [WalletManageAdminController::class, 'depositSettings'])->middleware(['permission:' . PermissionKey::ADMIN_WALLET_SETTINGS->value]);
                Route::get('list', [WalletManageAdminController::class, 'index']);
                Route::post('status', [WalletManageAdminController::class, 'status']);
                Route::post('deposit', [WalletManageAdminController::class, 'depositCreateByAdmin']);
                Route::get('transactions', [WalletManageAdminController::class, 'transactionRecords'])->middleware(['permission:' . PermissionKey::ADMIN_WALLET_TRANSACTION->value]);
                Route::post('transactions-status', [WalletManageAdminController::class, 'transactionStatus']);
                Route::post('transactions-payment-status-change', [WalletManageAdminController::class, 'transactionPaymentStatusChange']);
            });

            // withdrawals manage
            Route::group(['prefix' => 'withdraw/'], function () {

                // settings
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
            // Collect Cash (for cash collection)
            Route::match(['get', 'post'], 'cash-collection', [AdminCashCollectionController::class, 'collectCash'])->middleware('permission:' . PermissionKey::ADMIN_FINANCIAL_COLLECT_CASH->value);
        });


        // business-operations
        Route::group(['prefix' => 'business-operations/'], function () {
            // store type
            Route::group(['prefix' => 'store-type/', 'middleware' => 'permission:' . PermissionKey::ADMIN_STORE_TYPE_MANAGE->value], function () {
                Route::get('list', [AdminStoreTypeManageController::class, 'allStoreTypes']);
                Route::get('details/{id}', [AdminStoreTypeManageController::class, 'storeTypeDetails']);
                Route::post('update', [AdminStoreTypeManageController::class, 'updateStoreType']);
                Route::post('change-status', [AdminStoreTypeManageController::class, 'changeStatus']);
            });
            // area setup
            Route::prefix('area/')->middleware(['permission:' . PermissionKey::ADMIN_GEO_AREA_MANAGE->value])->group(function () {
                Route::get('list', [AdminAreaSetupManageController::class, 'index']);
                Route::post('add', [AdminAreaSetupManageController::class, 'store']);
                Route::post('update', [AdminAreaSetupManageController::class, 'update']);
                Route::get('details/{id}', [AdminAreaSetupManageController::class, 'show']);
                Route::post('change-status', [AdminAreaSetupManageController::class, 'changeStatus']);
                Route::delete('remove/{id}', [AdminAreaSetupManageController::class, 'destroy']);
                Route::post('settings/update', [AdminAreaSetupManageController::class, 'updateStoreAreaSetting']);
                Route::get('settings/details/{store_area_id}', [AdminAreaSetupManageController::class, 'storeAreaSettingsDetails']);
            });
            // commission Settings
            Route::prefix('commission')->middleware(['permission:' . PermissionKey::ADMIN_COMMISSION_SETTINGS->value])->group(function () {
                Route::match(['get', 'post'], '/settings', [AdminCommissionManageController::class, 'commissionSettings']);
            });
        });
        // report-analytics
        Route::group(['prefix' => 'report-analytics/'], function () {
            Route::get('reportList', [AdminReportAnalyticsManageController::class, 'reportList'])->middleware('permission:' . PermissionKey::ADMIN_REPORT_ANALYTICS_ORDER->value);
            Route::get('order', [AdminReportAnalyticsManageController::class, 'orderReport'])->middleware('permission:' . PermissionKey::ADMIN_REPORT_ANALYTICS_ORDER->value);
            Route::get('transaction', [AdminReportAnalyticsManageController::class, 'transactionReport'])->middleware('permission:' . PermissionKey::ADMIN_REPORT_ANALYTICS_ORDER->value);
        });

        /*--------------------- System management ----------------------------*/
        Route::group(['prefix' => 'system-management/'], function () {
            Route::match(['get', 'post'], '/general-settings', [SystemManagementController::class, 'generalSettings'])->middleware('permission:' . PermissionKey::GENERAL_SETTINGS->value);
            // all pages settings
            Route::group(['prefix' => 'page-settings/', 'middleware' => 'permission:' . PermissionKey::PAGE_SETTINGS->value], function () {
                Route::match(['get', 'post'], 'register', [PageSettingsManageController::class, 'registerSettings'])->middleware('permission:' . PermissionKey::REGISTER_PAGE_SETTINGS->value);
                Route::match(['get', 'post'], 'login', [PageSettingsManageController::class, 'loginSettings'])->middleware('permission:' . PermissionKey::LOGIN_PAGE_SETTINGS->value);
                Route::match(['get', 'post'], 'product-details', [PageSettingsManageController::class, 'ProductDetailsSettings'])->middleware('permission:' . PermissionKey::PRODUCT_DETAILS_PAGE_SETTINGS->value);
                Route::match(['get', 'post'], 'blog-details', [PageSettingsManageController::class, 'blogSettings'])->middleware('permission:' . PermissionKey::BLOG_PAGE_SETTINGS->value);
                Route::match(['get', 'post'], 'about', [AboutSettingsManageController::class, 'aboutSettings'])->middleware('permission:' . PermissionKey::ABOUT_PAGE_SETTINGS->value);
                Route::match(['get', 'post'], 'contact', [ContactSettingsManageController::class, 'contactSettings'])->middleware('permission:' . PermissionKey::CONTACT_PAGE_SETTINGS->value);
                Route::match(['get', 'post'], 'become-seller', [BecomeSellerSettingsController::class, 'becomeSellerSettings'])->middleware('permission:' . PermissionKey::BECOME_SELLER_PAGE_SETTINGS->value);
            });

            // menu manage
            Route::prefix('menu-customization/')->middleware(['permission:' . PermissionKey::MENU_CUSTOMIZATION->value])->group(function () {
                Route::get('list', [MenuManageController::class, 'index']);
                Route::post('store', [MenuManageController::class, 'store']);
                Route::get('details/{id}', [MenuManageController::class, 'show']);
                Route::post('update', [MenuManageController::class, 'update']);
                Route::post('update-position', [MenuManageController::class, 'updatePosition']);
                Route::delete('remove/{id?}', [MenuManageController::class, 'destroy']);
            });

            Route::match(['get', 'post'], '/footer-customization', [SystemManagementController::class, 'footerCustomization'])->middleware('permission:' . PermissionKey::FOOTER_CUSTOMIZATION->value);
            Route::match(['get', 'post'], '/maintenance-settings', [SystemManagementController::class, 'maintenanceSettings'])->middleware('permission:' . PermissionKey::MAINTENANCE_SETTINGS->value);
            Route::match(['get', 'post'], '/seo-settings', [SystemManagementController::class, 'seoSettings'])->middleware('permission:' . PermissionKey::SEO_SETTINGS->value);
            Route::match(['get', 'post'], '/gdpr-cookie-settings', [SystemManagementController::class, 'gdprCookieSettings'])->middleware('permission:' . PermissionKey::GDPR_COOKIE_SETTINGS->value);
            Route::match(['get', 'post'], '/firebase-settings', [SystemManagementController::class, 'firebaseSettings'])->middleware('permission:' . PermissionKey::FIREBASE_SETTINGS->value);
            Route::match(['get', 'post'], '/social-login-settings', [SystemManagementController::class, 'socialLoginSettings'])->middleware('permission:' . PermissionKey::SOCIAL_LOGIN_SETTINGS->value);
            Route::match(['get', 'post'], '/google-map-settings', [SystemManagementController::class, 'googleMapSettings'])->middleware('permission:' . PermissionKey::GOOGLE_MAP_SETTINGS->value);
            Route::match(['get', 'post'], '/recaptcha-settings', [SystemManagementController::class, 'recaptchaSettings'])->middleware('permission:' . PermissionKey::RECAPTCHA_SETTINGS->value);

            // database and cache settings
            Route::post('/cache-management', [SystemManagementController::class, 'cacheManagement'])->middleware('permission:' . PermissionKey::CACHE_MANAGEMENT->value);
            Route::post('/database-update-controls', [SystemManagementController::class, 'databaseUpdateControl'])->middleware('permission:' . PermissionKey::DATABASE_UPDATE_CONTROLS->value);
            // email settings
            Route::group(['middleware' => ['permission:' . PermissionKey::SMTP_SETTINGS->value]], function () {
                Route::match(['get', 'post'], '/email-settings/smtp', [EmailSettingsController::class, 'smtpSettings']);
                Route::post('/email-settings/test-mail-send', [EmailSettingsController::class, 'testMailSend']);
            });
            // email settings

            Route::group(['prefix' => 'email-settings/email-template/', 'middleware' => 'permission:' . PermissionKey::EMAIL_TEMPLATES->value], function () {
                Route::get('list', [EmailTemplateManageController::class, 'allEmailTemplate']);
                Route::post('add', [EmailTemplateManageController::class, 'addEmailTemplate']);
                Route::get('details/{id}', [EmailTemplateManageController::class, 'emailTemplateDetails']);
                Route::post('edit', [EmailTemplateManageController::class, 'editEmailTemplate']);
                Route::delete('remove/{id}', [EmailTemplateManageController::class, 'deleteEmailTemplate']);
                Route::post('change-status', [EmailTemplateManageController::class, 'changeStatus']);
            });
        });

        /*--------------------- Roles &  permissions manage ----------------------------*/
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::post('permissions-for-store-owner', [PermissionController::class, 'permissionForStoreOwner']);
        Route::get('module-wise-permissions', [PermissionController::class, 'moduleWisePermissions']);
        Route::group(['prefix' => 'roles/', 'middleware' => 'permission:' . PermissionKey::USERS_ROLE_ADD->value], function () {
            Route::get('list', [RoleController::class, 'index'])->middleware('permission:' . PermissionKey::USERS_ROLE_LIST->value);
            Route::post('add', [RoleController::class, 'store']);
            Route::get('details/{id}', [RoleController::class, 'show']);
            Route::post('update', [RoleController::class, 'update']);
            Route::post('change-status', [RoleController::class, 'changeStatus']);
            Route::delete('remove/{id}', [RoleController::class, 'destroy']);
        });
    });
});
