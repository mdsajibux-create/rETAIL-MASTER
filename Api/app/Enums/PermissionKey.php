<?php

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

enum PermissionKey: string
{
    use InvokableCases;
    use Values;

    case ADMIN_DASHBOARD = '/admin/dashboard';
    case ADMIN_POS_SALES = '/admin/pos';
    case ADMIN_POS_ORDERS = '/admin/pos/orders';
    case ADMIN_POS_SETTINGS = '/admin/pos/settings';
    case ADMIN_ORDERS_ALL = '/admin/orders';
    case ADMIN_ORDERS_RETURNED_OR_REFUND_REQUEST = '/admin/orders/refund-request';
    case ADMIN_ORDERS_RETURNED_OR_REFUND_REASON = '/admin/orders/refund-reason/list';
    case ADMIN_PRODUCTS_MANAGE = '/admin/product/list';
    case ADMIN_PRODUCTS_STOCK_MANAGE = '/admin/product/stock';
    case ADMIN_PRODUCTS_STOCK_TRANSFER_MANAGE = '/admin/product/stock/transfer';
    case ADMIN_PRODUCTS_TRASH_MANAGEMENT = '/admin/product/trash-list';
    case ADMIN_PRODUCT_STOCK_REPORT = '/admin/product/stock-report';
    case ADMIN_PRODUCT_PRODUCT_BULK_IMPORT = '/admin/product/import';
    case ADMIN_PRODUCT_INVENTORY = '/admin/product/inventory';
    case ADMIN_STORE_LIST = '/admin/branch/list';
    case ADMIN_BRANCH_ADD = '/admin/branch/add';
    case ADMIN_STORE_TRASH_MANAGEMENT = '/admin/branch/trash-list';
    case ADMIN_PROMOTIONAL_FLASH_SALE_MANAGE = '/admin/promotional/flash-deals/list';
    case ADMIN_MEDIA_MANAGE = '/admin/media-manage';
    case ADMIN_PRODUCT_BRAND_LIST = '/admin/brand/list';
    case ADMIN_PRODUCT_CATEGORY_LIST = '/admin/categories';
    case PRODUCT_ATTRIBUTE_ADD = '/admin/attribute';
    case PRODUCT_ATTRIBUTE_LIST = '/admin/attribute/list';
    case ADMIN_PRODUCT_AUTHORS_MANAGE = '/admin/product/author/list';
    case ADMIN_PRODUCT_TAG_LIST = '/admin/tag/list';
    case ADMIN_PRODUCT_UNIT_LIST = '/admin/unit/list';
    case ADMIN_DYNAMIC_FIELDS = '/admin/dynamic-fields';
    case ADMIN_SLIDER_MANAGE_LIST = '/admin/slider/list';
    case ADMIN_FEEDBACK_REVIEWS = '/admin/feedback-control/review';
    case ADMIN_FEEDBACK_QUESTIONS = '/admin/feedback-control/questions';
    case ADMIN_USERS_ROLE_ADD = '/admin/roles/add';
    case ADMIN_USERS_ROLE_LIST = '/admin/roles/list';
    case ADMIN_STATE_LIST = '/admin/state/list';
    case ADMIN_CITY_LIST = '/admin/city/list';
    case ADMIN_AREA_LIST = '/admin/area/list';
    case ADMIN_DELIVERYMAN_VEHICLE_TYPE = '/admin/deliveryman/vehicle-types/list';
    case ADMIN_DELIVERYMAN_MANAGE_LIST = '/admin/deliveryman/list';
    case ADMIN_DELIVERYMAN_TRASH_MANAGEMENT = '/admin/deliveryman/trash-list';
    case ADMIN_DELIVERYMAN_REQUEST = '/admin/deliveryman/request';
    case ADMIN_CUSTOMER_MANAGEMENT_LIST = '/admin/customer/list';
    case ADMIN_CUSTOMER_TRASH_MANAGEMENT = '/admin/customer/trash-list';
    case ADMIN_CUSTOMER_SUBSCRIBED_MAIL_LIST = '/admin/customer/subscriber-list';
    case ADMIN_CUSTOMER_CONTACT_MESSAGES = '/admin/customer/contact-messages';
    case ADMIN_FINANCIAL_WITHDRAW_MANAGE_HISTORY = '/admin/financial/withdraw/history';
    case ADMIN_FINANCIAL_WITHDRAW_MANAGE_SETTINGS = '/admin/financial/withdraw/settings';
    case ADMIN_FINANCIAL_WITHDRAW_MANAGE_REQUEST = '/admin/financial/withdraw/request';
    case ADMIN_WITHDRAW_METHOD_MANAGEMENT = '/admin/financial/withdraw/method/list';
    case ADMIN_FINANCIAL_COLLECT_CASH = '/admin/financial/cash-collect';
    case ADMIN_REPORT_ANALYTICS_ORDER = '/admin/report-analytics/order';
    case ADMIN_REPORT_ANALYTICS_TRANSACTION = '/admin/report-analytics/transaction';
    case ADMIN_STORE_TYPE_MANAGE = '/admin/business-operations/product-type';
    case ADMIN_GEO_AREA_MANAGE = '/admin/business-operations/area/list';
    case ADMIN_COMMISSION_SETTINGS = '/admin/business-operations/settings';
    case GENERAL_SETTINGS = '/admin/system-management/general-settings';
    case CURRENCIES_SETTINGS = '/admin/system-management/currencies/settings';
    case CURRENCIES_MANAGE = '/admin/system-management/currencies/manage';
    case APPEARANCE_SETTINGS = 'appearance_settings';
    case THEMES_SETTINGS = '/admin/system-management/appearance/themes';
    case MENU_CUSTOMIZATION = '/admin/system-management/appearance/menu-customization';
    case FOOTER_CUSTOMIZATION = '/admin/system-management/appearance/footer-customization';
    case MAINTENANCE_SETTINGS = '/admin/system-management/maintenance-settings';
    case SMTP_SETTINGS = '/admin/system-management/email-settings/smtp';
    case EMAIL_TEMPLATES = '/admin/system-management/email-settings/email-template/list';
    case ADMIN_LANGUAGES = '/admin/system-management/languages';
    case SEO_SETTINGS = '/admin/system-management/seo-settings';
    case SITEMAP_SETTINGS = '/admin/system-management/sitemap-settings';

    case GDPR_COOKIE_SETTINGS = '/admin/system-management/gdpr-cookie-settings';
    case CACHE_MANAGEMENT = '/admin/system-management/cache-management';
    case DATABASE_UPDATE_CONTROLS = '/admin/system-management/database-update-controls';
    case OPEN_AI_SETTINGS = '/admin/system-management/openai-settings';
    case GOOGLE_MAP_SETTINGS = '/admin/system-management/google-map-settings';
    case FIREBASE_SETTINGS = '/admin/system-management/firebase-settings';
    case SOCIAL_LOGIN_SETTINGS = '/admin/system-management/social-login-settings';
    case RECAPTCHA_SETTINGS = '/admin/system-management/recaptcha-settings';
    case ADMIN_STAFF_LIST = '/admin/staff/list';
    case ADMIN_STAFF_MANAGE = '/admin/staff/add';
    case ADMIN_BLOG_CATEGORY_MANAGE = '/admin/blog/category';
    case ADMIN_BLOG_MANAGE = '/admin/blog/posts';
    case ADMIN_CHAT_SETTINGS = '/admin/chat/settings';
    case ADMIN_CHAT_MANAGE = '/admin/chat/manage';
    case ADMIN_SMS_GATEWAY_SETTINGS = '/admin/sms-provider/settings';
    case ADMIN_TICKETS_DEPARTMENT = '/admin/ticket/department';
    case ADMIN_SUPPORT_TICKETS_MANAGE = '/admin/support-ticket/list';
    case ADMIN_PAGES_LIST = '/admin/pages/list';
    case ADMIN_PAYMENT_SETTINGS = '/admin/payment-gateways/settings';
    case ADMIN_INTEGRATION_SETTINGS = '/admin/integration';
    case ADMIN_COUPON_MANAGE = '/admin/coupon/list';
    case ADMIN_COUPON_LINE_MANAGE = '/admin/coupon-line/list';
    case ADMIN_WALLET_MANAGE = '/admin/wallet/list';
    case ADMIN_WALLET_TRASH_MANAGEMENT = '/admin/wallet/trash-list';
    case ADMIN_WALLET_TRANSACTION = '/admin/wallet/transactions';
    case ADMIN_WALLET_SETTINGS = '/admin/wallet/settings';
    case ADMIN_NOTIFICATION_MANAGEMENT = '/admin/notifications';


    // ----------- Branch Product Manage
    case SELLER_STORE_DASHBOARD = 'branch/dashboard';
    case BRANCH_POS_SALES = '/branch/pos';
    case BRANCH_POS_ORDERS = '/branch/pos/orders';
    case BRANCH_PRODUCT_LIST = '/branch/product/list';
    case BRANCH_PRODUCT_INVENTORY = '/branch/product/inventory';
    case BRANCH_PRODUCT_STOCK_MANAGE = '/branch/product/stock';
    case BRANCH_PRODUCT_STOCK_TRANSFER_MANAGE = '/branch/product/transfer';
    case BRANCH_STAFF_MANAGE = '/branch/staff/list';
    case BRANCH_ORDER_MANAGE = '/branch/orders';
    case SELLER_ORDERS_RETURNED_OR_REFUND_REQUEST = '/branch/orders/refund-request';
    case BRANCH_NOTIFICATION_MANAGEMENT = '/branch/notifications';
    case SELLER_CHAT_MANAGE = '/branch/chat/list';
    case SELLER_STORE_SUPPORT_TICKETS_MANAGE = '/branch/support-ticket/list';
    case DELIVERYMAN_FINANCIAL_WITHDRAWALS = '/deliveryman/withdraw-manage';
}
