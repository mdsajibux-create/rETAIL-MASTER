<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProductType;
use App\Http\Resources\Admin\CurrencyDetailsResource;
use App\Http\Resources\Com\FooterSettingsPublicResource;
use App\Http\Resources\Com\GdprPublicResource;
use App\Http\Resources\Com\GeneralSettingOptionResource;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Com\ProductCategoryPublicSettingResource;
use App\Http\Resources\MenuPublicViewResource;
use App\Http\Resources\PageDetailsPublicResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\SliderPublicResource;
use App\Http\Resources\Translation\ThemeSettingsTranslationResource;
use App\Models\Language;
use App\Models\Slider;
use App\Models\SystemCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Branch\app\Models\Branch;
use Modules\Catalog\app\Models\ProductCategory;
use Modules\PaymentGateways\app\Models\Currency;
use Modules\SystemCore\app\Models\Menu;
use Modules\SystemCore\app\Models\Page;
use Modules\SystemCore\app\Models\SettingOption;

class FrontendSettingsController
{

    private function getSettingsByKeys(array $keys): array
    {
        $cacheKey = 'setting_options:' . md5(implode('|', $keys));

        return Cache::remember($cacheKey, 86400, function () use ($keys) {
            return SettingOption::query()
                ->whereIn('option_name', $keys)
                ->pluck('option_value', 'option_name')
                ->toArray();
        });
    }

    public function settingsData(Request $request)
    {
        $language = $request->language ?? config('app.default_language');
        $themeName = config('themes.active_theme') ?? config('themes.default_theme') ?? '';
        $platform  = $request->platform ?? 'web';

        $ttlSeconds = 24 * 60 * 60;  // TTL in seconds (1 day)
        // Cache key includes theme, language and platform
        $cacheKey = "home_settings_data:" . implode(':', [
                "theme={$themeName}",
                "lang={$language}",
                "platform={$platform}",
                "filter=" . ($request->filter ?? 'all'),
                "menu_page=" . ($request->menu_per_page ?? 10),
                "menu_search=" . ($request->menu_search ?? ''),
            ]);

        // Get cached data or generate it
        $data = Cache::remember($cacheKey, $ttlSeconds, function () use ($language, $themeName, $platform, $request) {

            //  Batch all SettingOption queries into ONE
            $settingOptions = SettingOption::with(['translations', 'related_translations'])
                ->whereIn('option_name', [$themeName, 'footer_settings', 'gdpr_data'])
                ->get()
                ->keyBy('option_name');

            $theme_settings   = $settingOptions->get($themeName);
            $footer_settings  = $settingOptions->get('footer_settings');
            $gdprSettings     = $settingOptions->get('gdpr_data');

            // theme
            $theme_data = json_decode($theme_settings->option_value, true);
            $theme_settings->option_value = jsonImageModifierFormatter($theme_data);
            $translations = $theme_settings->translations
                ->groupBy('language')
                ->map(function ($item) use ($theme_settings) {
                    $item->theme_slug = $theme_settings['option_value']['slug'];
                    return $item;
                });


            // footer settings
            $json_decoded = json_decode($footer_settings->option_value, true);
            $content = jsonImageModifierFormatter($json_decoded);
            $footer_settings->option_value = $content;

            // gdpr cookie settings
            $json_decoded = json_decode($gdprSettings->option_value, true);
            $content = jsonImageModifierFormatter($json_decoded);
            $gdprSettings->option_value = $content;

         // menu settings
        $per_page = $request->menu_per_page ?? 10;
        $search = $request->menu_search;
        $isPaginationDisabled = $request->has('menu_pagination') && $request->menu_pagination === "false";

        $menus = Menu::leftJoin('translations', function ($join) use ($language) {
            $join->on('menus.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', Menu::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'name');
        })->select(
            'menus.*',
            DB::raw('COALESCE(translations.value, menus.name) as name')
        );

        // Apply search filter if search parameter exists
        if ($search) {
            $menus->where(function ($query) use ($search) {
                $query->where('translations.value', 'like', "%{$search}%")
                    ->orWhere('menus.name', 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        $sortField = $request->menu_sortField ?? 'position';
        $sortOrder = $request->menu_sort ?? 'asc';
        if ($isPaginationDisabled) {
            $menus = $menus
                ->where('is_visible', true)
                ->with('childrenRecursive')
                ->whereNull('parent_id')
                ->orderBy($sortField, $sortOrder)
                ->get();
            $menu_data = [
                'menus' => MenuPublicViewResource::collection($menus),
            ];
        } else {
            $menus = $menus
                ->orderBy($sortField, $sortOrder)
                ->paginate($per_page);

            $menu_data = [
                'menus' => MenuPublicViewResource::collection($menus),
                'meta' => new PaginationResource($menus)
            ];
        }


       // deliveryman earning settings
        $system_charge = SystemCharge::first();
        $zone_system_enable = $system_charge->zone_system_enable;
        $deliveryman_earning_type = $system_charge->deliveryman_earning_type ?? 'salary';


        // site general info
        $filter_data = $request->filter;
        if ($filter_data == 'logo') {
            $keys = [
                'com_site_logo',
                'com_site_white_logo',
                'com_site_favicon',
            ];
        } else {
            $keys = [
                'com_site_title',
                'com_site_subtitle',
                'com_site_favicon',
                'com_site_logo',
                'com_site_white_logo',
                'com_site_footer_copyright',
                'com_site_email',
                'com_site_website_url',
                'com_site_contact_number',
                'com_site_full_address',
                'com_maintenance_mode',
                'com_user_login_otp',
                'com_user_email_verification',
                'com_google_recaptcha_v3_site_key',
                'com_google_recaptcha_v3_secret_key',
                'com_google_recaptcha_enable_disable',
                'otp_login_enabled_disable',
                'com_google_login_enabled',
                'com_facebook_login_enabled',
                'com_openai_enable_disable',
                'com_home_one_category_button_title',
                'com_home_one_store_button_title',
                'com_google_map_enable_disable',
                'com_pos_settings_print_invoice',
                // currency settings
                'com_site_global_currency',
                'com_site_currency_symbol_position',
                'com_site_comma_form_adjustment_amount',
                'com_site_enable_disable_decimal_point',
                'com_site_space_between_amount_and_symbol'
            ];
        }

            $options = $this->getSettingsByKeys($keys);

            $site_settings = [
                'com_site_logo'                       => $options['com_site_logo'] ?? null,
                'com_site_white_logo'                 => $options['com_site_white_logo'] ?? null,
                'com_site_favicon'                    => $options['com_site_favicon'] ?? null,
                'com_site_title'                      => $options['com_site_title'] ?? null,
                'com_site_subtitle'                   => $options['com_site_subtitle'] ?? null,
                'com_site_footer_copyright'           => $options['com_site_footer_copyright'] ?? null,
                'com_site_email'                      => $options['com_site_email'] ?? null,
                'com_site_website_url'                => $options['com_site_website_url'] ?? null,
                'com_site_contact_number'             => $options['com_site_contact_number'] ?? null,
                'com_site_full_address'               => $options['com_site_full_address'] ?? null,
                'com_maintenance_mode'                => $options['com_maintenance_mode'] ?? null,
                'com_user_login_otp'                  => $options['com_user_login_otp'] ?? null,
                'com_user_email_verification'         => $options['com_user_email_verification'] ?? null,
                'com_google_recaptcha_v3_site_key'    => $options['com_google_recaptcha_v3_site_key'] ?? null,
                'com_google_recaptcha_v3_secret_key'   => $options['com_google_recaptcha_v3_secret_key'] ?? null,
                'com_google_recaptcha_enable_disable'  => $options['com_google_recaptcha_enable_disable'] ?? null,
                'otp_login_enabled_disable'           => $options['otp_login_enabled_disable'] ?? null,
                'com_google_login_enabled'            => $options['com_google_login_enabled'] ?? null,
                'com_facebook_login_enabled'           => $options['com_facebook_login_enabled'] ?? null,
                'com_openai_enable_disable'           => $options['com_openai_enable_disable'] ?? null,
                'com_home_one_category_button_title'  => $options['com_home_one_category_button_title'] ?? null,
                'com_home_one_store_button_title'     => $options['com_home_one_store_button_title'] ?? null,
                'active_theme'                        => $themeName ?: 'theme_one',
                'deliveryman_earning_type'            => $deliveryman_earning_type,
                'com_google_map_enable_disable'       => $options['com_google_map_enable_disable'] ?? null,
                'com_pos_settings_print_invoice'       => $options['com_pos_settings_print_invoice'] ?? null,
            ];



        // currency settings
        $query = Currency::with('translations')->latest();
        $search = $request->search;
        if ($request->has('search') && $search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }


        $currencies_lists = $query->get();
        $currencies_info = [
            'com_site_global_currency' => $options['com_site_global_currency'] ?? null,
            'com_site_currency_symbol_position' => $options['com_site_currency_symbol_position'] ?? null,
            'com_site_comma_form_adjustment_amount' => $options['com_site_comma_form_adjustment_amount'] ?? null,
            'com_site_enable_disable_decimal_point' => $options['com_site_enable_disable_decimal_point'] ?? null,
            'com_site_space_between_amount_and_symbol' => $options['com_site_space_between_amount_and_symbol'] ?? null,
        ];

            // all languages
            $baseQuery = Language::where('status', 'active');
            $languages_list = (clone $baseQuery)
                ->select('code', 'name', 'is_default')
                ->get();

            $languages = (clone $baseQuery)->limit(10)->get();
            $languages->transform(function ($lang) {
                if ($lang->translations) {
                    $lang->translations = json_decode($lang->translations, true);
                }
                return $lang;
            });


            // Product types
            $dropdown = ProductType::dropdown();

            $main_branch = Branch::select(
                'id',
                'name',
                'zone_id',
                'type',
                'is_main',
                'latitude',
                'longitude'
            )->where('is_main', true)->first();

            // sliders
            $sliders = Slider::with('related_translations')
                ->where('status', 1)
                ->where('theme_name', getThemeProductType() === 'furniture' ? 'one' : 'two')
                ->where('platform', $platform)
                ->orderBy('order', 'asc')
                ->limit(15)
                ->get();
;

            $categories = ProductCategory::leftJoin('translations', function ($join) use ($language) {
                $join->on('product_category.id', '=', 'translations.translatable_id')
                    ->where('translations.translatable_type', '=', ProductCategory::class)
                    ->where('translations.language', '=', $language)
                    ->where('translations.key', '=', 'category_name');
            })->select(
                    'product_category.id',
                    'product_category.category_name',
                    'product_category.type',
                    'product_category.parent_path',
                    'product_category.parent_id',
                    'product_category.category_slug',
                    'product_category.category_thumb',
                    'product_category.status',
                    DB::raw('COALESCE(translations.value, product_category.category_name) as translated_category_name')
                )
                ->where('product_category.type', getThemeProductType())
                ->whereNull('parent_id')
                ->where('status', 1)
                ->limit(100)
                ->get();


            // Return raw data array (not JSON response)
            return [
                'themes' => [
                    "theme_data" => $theme_settings->option_value,
                    "translations"  => ThemeSettingsTranslationResource::collection($translations)->toArray($request),
                ],
                'menu_data' => [$menu_data],
                'currencies' => [
                    'info' => $currencies_info,
                    'lists' => CurrencyDetailsResource::collection($currencies_lists),
                ],
                'categories' => ProductCategoryPublicSettingResource::collection($categories),
                'footer' => new FooterSettingsPublicResource($footer_settings),
                'general_info' => new GeneralSettingOptionResource($site_settings),
                'google_map_settings' => $site_settings['com_google_map_enable_disable'] ?? null,
                'cookie_settings' => new GdprPublicResource($gdprSettings),
                'product_types' => $dropdown,
                'languages_list' => $languages_list,
                'languages' => $languages,
                'main_branch' => $main_branch,
                'zone_system_enable' => $zone_system_enable,
                'pos_setting' => $site_settings['com_pos_settings_print_invoice'] ?? null,
                'order_tax' => $system_charge->order_tax,
                'sliders' => SliderPublicResource::collection($sliders)->toArray($request),
            ];
        });

        // Wrap in JSON response after cache
        return response()->json($data);
    }

    public function languageData(Request $request)
    {
        $language  = $request->language ?? 'en';
        $platform  = $request->platform ?? 'web';

        $ttlSeconds = 24 * 60 * 60;  // TTL in seconds (1 day)
        // Cache key includes theme, language and platform
        $cacheKey = "home_settings_data:" . implode(':', ["lang={$language}", "platform={$platform}"]);

        // Get cached data or generate it
        $data = Cache::remember($cacheKey, $ttlSeconds, function () use ($language, $platform, $request) {
            // language
            $language = Language::where('status', 'active')
                ->where('code', $language)
                ->first();

            if ($language?->translations) {
                $language->translations = json_decode($language->translations, true);
            }

            // Return raw data array
            return [
                'languages' => $language,
            ];
        });

        // Wrap in JSON response after cache
        return response()->json($data);
    }

    public function singlePageDetails(Request $request, $slug)
    {
        // Try to find page with default theme first
        $config_theme = config('themes.active_theme') ?? config('themes.default_theme');
        $page = Page::where('slug', $slug)
            ->where('theme_name', $config_theme)
            ->where('status', 'publish')
            ->first();

        // If not found, try with requested theme
        if (!$page) {
            $page = Page::with('related_translations')
                ->where('slug', $slug)
                ->where('theme_name', $request->theme_name ?? 'default')
                ->where('status', 'publish')
                ->first();
        }

        // Return 404 if page not found
        if (!$page) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

        // Process content (decode JSON and format images)
        $processedContent = is_string($page->content) ? json_decode($page->content, true) : $page->content;
        $formattedContent = is_array($processedContent) ? jsonImageModifierFormatter($processedContent) : [];

        $page->content = $formattedContent;

        return response()->json(new PageDetailsPublicResource($page));
    }

    public function allPages(Request $request)
    {
        $pages = Page::with('related_translations')
            ->where('status', 'publish')
            ->take(200)->get();

        return response()->json([
            'all_pages' => PageListResource::collection($pages),
        ]);
    }

}
