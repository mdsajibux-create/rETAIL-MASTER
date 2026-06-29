<?php

namespace App\Http\Controllers\Api\V1\Com;

use App\Actions\ImageModifier;
use App\Http\Controllers\Api\V1\Controller;
use App\Interfaces\TranslationInterface;
use Illuminate\Http\Request;
use Modules\SystemCore\app\Models\SettingOption;

class FrontendPageSettingsController extends Controller
{

    public function __construct(
        protected TranslationInterface $transRepo,
        protected SettingOption        $get_com_option,
    )
    {
    }

    public function translationKeys(): mixed
    {
        return $this->get_com_option->translationKeys;
    }

    public function RegisterPageSettings(Request $request)
    {
        $language = $request->input('language', 'en'); // Default language is 'en'

        $ComOptionGet = SettingOption::with('translations')
            ->whereIn('option_name', [
                'com_register_page_title',
                'com_register_page_subtitle',
                'com_register_page_description',
                'com_register_page_terms_title',
            ])->get();

        // Default settings
        $page_settings = [
            'com_register_page_title' => com_option_get('com_register_page_title') ?? '',
            'com_register_page_subtitle' => com_option_get('com_register_page_subtitle') ?? '',
            'com_register_page_description' => com_option_get('com_register_page_description') ?? '',
            'com_register_page_image' => ImageModifier::generateImageUrl(com_option_get('com_register_page_image')),
            'com_register_page_terms_page' => com_option_get('com_register_page_terms_page') ?? '',
            'com_register_page_terms_title' => com_option_get('com_register_page_terms_title') ?? '',
            'com_register_page_social_enable_disable' => com_option_get('com_register_page_social_enable_disable') ?? '',
        ];

        // Replace with translation values based on requested language
        foreach ($ComOptionGet as $settingOption) {
            $translation = $settingOption->translations->where('language', $language)->first();

            if ($translation) {
                $page_settings[$settingOption->option_name] = trim($translation->value, '"');
            }
        }

        return response()->json(['data' => $page_settings]);
    }




    public function LoginPageSettings(Request $request)
    {
        $language = $request->input('language', 'en'); // Default language is 'en'

        $ComOptionGet = SettingOption::with('translations')
            ->whereIn('option_name', [
                'com_login_page_title',
                'com_login_page_subtitle',
                'com_seller_login_page_title',
                'com_seller_login_page_subtitle',
            ])->get();

        // Default settings
        $page_settings = [
            'com_login_page_title' => com_option_get('com_login_page_title') ?? '',
            'com_login_page_subtitle' => com_option_get('com_login_page_subtitle') ?? '',
            'com_login_page_social_enable_disable' => com_option_get('com_login_page_social_enable_disable') ?? '',
            'com_login_page_image' => ImageModifier::generateImageUrl(com_option_get('com_login_page_image')),
            'com_site_logo' => ImageModifier::generateImageUrl(com_option_get('com_site_logo')),
            // admin login
            'com_seller_login_page_title' => com_option_get('com_seller_login_page_title') ?? '',
            'com_seller_login_page_subtitle' => com_option_get('com_seller_login_page_subtitle') ?? '',
            'com_seller_login_page_social_enable_disable' => com_option_get('com_seller_login_page_social_enable_disable') ?? '',
            'com_seller_login_page_image' => ImageModifier::generateImageUrl(com_option_get('com_seller_login_page_image')),
        ];

        // Replace with translation values based on requested language
        foreach ($ComOptionGet as $settingOption) {
            $translation = $settingOption->translations->where('language', $language)->first();

            if ($translation) {
                $page_settings[$settingOption->option_name] = trim($translation->value, '"');
            }
        }
        return response()->json(['data' => $page_settings]);
    }

    public function productDetailsPageSettings(Request $request)
    {
        $language = $request->input('language', 'en'); // Default language is 'en'

        $ComOptionGet = SettingOption::with('translations')->whereIn('option_name', [
            'com_product_details_page_delivery_title',
            'com_product_details_page_delivery_subtitle',
            'com_product_details_page_return_refund_title',
            'com_product_details_page_return_refund_subtitle',
            'com_product_details_page_related_title'
        ])->get();

        // Default settings
        $page_settings = [
            'com_product_details_page_delivery_title' => com_option_get('com_product_details_page_delivery_title') ?? '',
            'com_product_details_page_delivery_subtitle' => com_option_get('com_product_details_page_delivery_subtitle') ?? '',
            'com_product_details_page_delivery_url' => com_option_get('com_product_details_page_delivery_url') ?? '',
            'com_product_details_page_delivery_enable_disable' => com_option_get('com_product_details_page_delivery_enable_disable') ?? '',
            'com_product_details_page_return_refund_title' => com_option_get('com_product_details_page_return_refund_title') ?? '',
            'com_product_details_page_return_refund_subtitle' => com_option_get('com_product_details_page_return_refund_subtitle') ?? '',
            'com_product_details_page_return_refund_url' => com_option_get('com_product_details_page_return_refund_url') ?? '',
            'com_product_details_page_return_refund_enable_disable' => com_option_get('com_product_details_page_return_refund_enable_disable') ?? '',
            'com_product_details_page_related_title' => com_option_get('com_product_details_page_related_title') ?? '',
        ];

        // Replace with translation values based on requested language
        foreach ($ComOptionGet as $settingOption) {
            $translation = $settingOption->translations->where('language', $language)->first();

            if ($translation) {
                $page_settings[$settingOption->option_name] = trim($translation->value, '"');
            }
        }

        return response()->json(['data' => $page_settings]);
    }


}