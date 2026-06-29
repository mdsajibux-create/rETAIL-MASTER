<?php

use App\Enums\PermissionKey;
use App\Http\Controllers\Api\V1\MediaController;
use Illuminate\Support\Facades\Route;
use Modules\SystemCore\app\Http\Controllers\Api\V1\AdminSitemapController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\EmailSettingsController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\EmailTemplateManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\ManageLanguageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\MenuManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\PagesManageController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\SystemManagementController;
use Modules\SystemCore\app\Http\Controllers\Api\V1\ThemeManageController;

/*--------------------- System management ----------------------------*/
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::prefix('admin/')->group(function () {
            // system-management routes
            Route::prefix('system-management/')->group(function () {
                Route::match(['get', 'post'], '/general-settings', [SystemManagementController::class, 'generalSettings'])->middleware('permission:' . PermissionKey::GENERAL_SETTINGS->value);
                // theme manage
                Route::group(['prefix' => 'appearance/'], function () {
                    Route::prefix('themes/')->middleware(['permission:' . PermissionKey::THEMES_SETTINGS->value])->group(function () {
                        Route::get('list', [ThemeManageController::class, 'themes']);
                        Route::post('store', [ThemeManageController::class, 'storeTheme']);
                        Route::get('details/{theme_slug?}', [ThemeManageController::class, 'themeDataGet']);
                        // theme active/deactivate
                        Route::patch('active', [ThemeManageController::class, 'themeActive']);
                    });

                    // menu manage
                    Route::prefix('menu-customization/')->middleware(['permission:' . PermissionKey::MENU_CUSTOMIZATION->value])->group(function () {
                        Route::get('list', [MenuManageController::class, 'menus']);
                        Route::post('store', [MenuManageController::class, 'store']);
                        Route::get('details/{id}', [MenuManageController::class, 'show']);
                        Route::post('update', [MenuManageController::class, 'update']);
                        Route::post('update-position', [MenuManageController::class, 'updatePosition']);
                        Route::delete('remove/{id?}', [MenuManageController::class, 'destroy']);
                    });
                    // footer customization
                    Route::match(['get', 'post'], '/footer-customization', [SystemManagementController::class, 'footerCustomization'])->middleware('permission:' . PermissionKey::FOOTER_CUSTOMIZATION->value);
                });

                Route::match(['get', 'post'], '/maintenance-settings', [SystemManagementController::class, 'maintenanceSettings'])->middleware('permission:' . PermissionKey::MAINTENANCE_SETTINGS->value);
                Route::match(['get', 'post'], '/seo-settings', [SystemManagementController::class, 'seoSettings'])->middleware('permission:' . PermissionKey::SEO_SETTINGS->value);
                Route::match(['get', 'post'], '/gdpr-cookie-settings', [SystemManagementController::class, 'gdprCookieSettings'])->middleware('permission:' . PermissionKey::GDPR_COOKIE_SETTINGS->value);
                Route::match(['get', 'post'], '/firebase-settings', [SystemManagementController::class, 'firebaseSettings'])->middleware('permission:' . PermissionKey::ADMIN_INTEGRATION_SETTINGS->value);
                Route::match(['get', 'post'], '/social-login-settings', [SystemManagementController::class, 'socialLoginSettings'])->middleware('permission:' . PermissionKey::SOCIAL_LOGIN_SETTINGS->value);
                Route::match(['get', 'post'], '/openai-settings', [SystemManagementController::class, 'openAiSettings'])->middleware('permission:' . PermissionKey::GOOGLE_MAP_SETTINGS->value);
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

                // languages
                Route::group(['prefix' => 'languages/', 'middleware' => 'permission:' . PermissionKey::ADMIN_LANGUAGES->value], function () {
                    Route::get('list', [ManageLanguageController::class, 'list']);
                    Route::post('add', [ManageLanguageController::class, 'addLanguage']);
                    Route::get('details/{id}', [ManageLanguageController::class, 'showLanguage']);
                    Route::post('update', [ManageLanguageController::class, 'updateLanguage']);
                    Route::delete('remove', [ManageLanguageController::class, 'deleteLanguage']);
                    Route::post('change-status', [ManageLanguageController::class, 'changeStatus']);

                    // key add
                    Route::post('key/search', [ManageLanguageController::class, 'searchKey']);
                    Route::post('key/update', [ManageLanguageController::class, 'updateKey']);
                    Route::post('key/add', [ManageLanguageController::class, 'addKey']);
                    Route::post('key/remove', [ManageLanguageController::class, 'removeKey']);
                });

                // media manage
                Route::group(['prefix' => 'media-manage/', 'middleware' => ['permission:' . PermissionKey::ADMIN_MEDIA_MANAGE->value]], function () {
                    Route::get('/', [MediaController::class, 'allMediaManage']);
                    Route::post('delete', [MediaController::class, 'mediaFileDelete']);
                });

                Route::match(['get', 'post'], 'sitemap-settings', [AdminSitemapController::class, 'generate'])
                    ->middleware('permission:' . PermissionKey::SITEMAP_SETTINGS->value);
            });

        // Pages manage routes
        Route::group(['middleware' => ['permission:' . PermissionKey::ADMIN_PAGES_LIST->value]], function () {
            Route::get('pages/list', [PagesManageController::class, 'listPages']);
            Route::post('pages/store', [PagesManageController::class, 'createPage']);
            Route::get('pages/details/{id}', [PagesManageController::class, 'getPageById']);
            Route::post('pages/update', [PagesManageController::class, 'updatePage']);
            Route::post('pages/duplicate', [PagesManageController::class, 'duplicatePage']);
            Route::delete('pages/remove/{id}', [PagesManageController::class, 'deletePage']);
        });

    });
});