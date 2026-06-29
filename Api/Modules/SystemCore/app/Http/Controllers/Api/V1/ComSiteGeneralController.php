<?php

namespace Modules\SystemCore\app\Http\Controllers\Api\V1;

use App\Actions\ImageModifier;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\CurrencyDetailsResource;
use App\Http\Resources\Com\SiteGeneralInfoFilterLogoResource;
use App\Http\Resources\Com\SiteGeneralInfoResource;
use Illuminate\Http\Request;
use Modules\BusinessSettings\app\Models\ProductType;
use Modules\BusinessSettings\app\Transformers\StoreTypeDropdownPublicResource;
use Modules\PaymentGateways\app\Models\Currency;

class ComSiteGeneralController extends Controller
{
    public function siteGeneralInfo(Request $request)
    {

        $filter_data = $request->filter;
        if ($filter_data == 'logo') {
            $site_settings = [
                'com_site_logo' => com_option_get('com_site_logo'),
                'com_site_white_logo' => com_option_get('com_site_white_logo'),
                'com_site_favicon' => com_option_get('com_site_favicon'),
            ];
            return response()->json([
                'site_settings' => new SiteGeneralInfoFilterLogoResource($site_settings),
            ]);
        } else {
            try {
                // check config
                $config_theme = config('themes.active_theme') ?? config('themes.default_theme');
            }catch (\Exception $exception){}

            $site_settings = [
                'com_site_title' => com_option_get('com_site_title'),
                'com_site_subtitle' => com_option_get('com_site_subtitle'),
                'com_site_favicon' => com_option_get('com_site_favicon'),
                'com_site_logo' => com_option_get('com_site_logo'),
                'com_site_white_logo' => com_option_get('com_site_white_logo'),
                'com_site_footer_copyright' => com_option_get('com_site_footer_copyright'),
                'com_site_email' => com_option_get('com_site_email'),
                'com_site_website_url' => com_option_get('com_site_website_url'),
                'com_site_contact_number' => com_option_get('com_site_contact_number'),
                'com_site_full_address' => com_option_get('com_site_full_address'),
                'com_maintenance_mode' => com_option_get('com_maintenance_mode'),
                'com_user_login_otp' => com_option_get('com_user_login_otp'),
                'com_user_email_verification' => com_option_get('com_user_email_verification'),
                'com_google_recaptcha_v3_site_key' => com_option_get('com_google_recaptcha_v3_site_key'),
                'com_google_recaptcha_v3_secret_key' => com_option_get('com_google_recaptcha_v3_secret_key'),
                'com_google_recaptcha_enable_disable' => com_option_get('com_google_recaptcha_enable_disable'),
                'otp_login_enabled_disable' => com_option_get('otp_login_enabled_disable'),
                'com_google_login_enabled' => com_option_get('com_google_login_enabled'),
                'com_facebook_login_enabled' => com_option_get('com_facebook_login_enabled'),
                'active_theme' => $config_theme ?? 'theme_one',
                'com_openai_enable_disable' =>com_option_get('com_openai_enable_disable'),
            ];
        }

        $currencies = [
            'com_site_global_currency' => com_option_get('com_site_global_currency'),
            'com_site_currency_symbol_position' => com_option_get('com_site_currency_symbol_position'),
            'com_site_comma_form_adjustment_amount' => com_option_get('com_site_comma_form_adjustment_amount'),
            'com_site_enable_disable_decimal_point' => com_option_get('com_site_enable_disable_decimal_point'),
            'com_site_space_between_amount_and_symbol' => com_option_get('com_site_space_between_amount_and_symbol'),
        ];

        $product_types = ProductType::where('status', 1)->get();

        return response()->json([
            'site_settings' => new SiteGeneralInfoResource($site_settings),
            'currencies_info' => $currencies,
            'product_types' => StoreTypeDropdownPublicResource::collection($product_types),
        ]);
    }

    public function currencyList(Request $request)
    {
        $query = Currency::with('translations')->latest();
        // Apply search if 'q' parameter exists
        $search = $request->search;
        if ($request->has('search') && $search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }
        $currencies = $query->get();
        return response()->json([
            'data' => CurrencyDetailsResource::collection($currencies),
        ]);
    }

    public function siteMaintenancePage(Request $request)
    {
        $settings = [
            'com_maintenance_title' => com_option_get('com_maintenance_title'),
            'com_maintenance_description' => com_option_get('com_maintenance_description'),
            'com_maintenance_start_date' => com_option_get('com_maintenance_end_date'),
            'com_maintenance_end_date' => com_option_get('com_maintenance_end_date'),
            'com_maintenance_image' => ImageModifier::generateImageUrl(com_option_get('com_maintenance_image'))
        ];
        return response()->json([
            'maintenance_settings' => $settings,
        ]);
    }

    public function googleMapSettings(Request $request)
    {
        $com_google_map_enable_disable = com_option_get('com_google_map_enable_disable');
        return $this->success([
            'com_google_map_enable_disable' => $com_google_map_enable_disable,
        ]);
    }

}
