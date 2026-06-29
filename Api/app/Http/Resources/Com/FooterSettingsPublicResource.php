<?php

namespace App\Http\Resources\Com;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FooterSettingsPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');

        // Always the source of truth for language-independent fields
        $optionValue = $this->option_value;
        $data = is_string($optionValue) ? json_decode($optionValue, true) : (array) $optionValue;

        // Get translation
        $translation = $this->translations
            ->where('language', $language)
            ->where('key', 'content')
            ->first()
            ?? $this->translations
                ->where('language', 'en')
                ->where('key', 'content')
                ->first();

        $translatedData = $translation ? json_decode($translation->value, true) : [];

        // Resolve translatable fields (suffixed keys on live, plain keys on local)
        $trans = fn($key) => $translatedData["{$key}_{$language}"]
            ?? $translatedData["{$key}_en"]
            ?? $translatedData[$key]
            ?? $data[$key]
            ?? null;

        $content = [
            //  Always from option_value
            'com_social_links_facebook_url'      => $data['com_social_links_facebook_url'] ?? null,
            'com_social_links_twitter_url'       => $data['com_social_links_twitter_url'] ?? null,
            'com_social_links_instagram_url'     => $data['com_social_links_instagram_url'] ?? null,
            'com_social_links_linkedin_url'      => $data['com_social_links_linkedin_url'] ?? null,
            'com_payment_methods_enable_disable' => $data['com_payment_methods_enable_disable'] ?? null,
            'com_features_enable_disable'        => $data['com_features_enable_disable'] ?? null,
            'com_resources_enable_disable'       => $data['com_resources_enable_disable'] ?? null,
            'com_support_enable_disable'         => $data['com_support_enable_disable'] ?? null,
            'com_company_enable_disable'         => $data['com_company_enable_disable'] ?? null,
            'com_social_links_enable_disable'    => $data['com_social_links_enable_disable'] ?? null,
            'com_social_links_title'             => $data['com_social_links_title'] ?? null,
            'com_payment_methods_image'          => $data['com_payment_methods_image'] ?? null,
            'com_payment_methods_image_url'      => $data['com_payment_methods_image_url'] ?? null,
            'com_news_letter_theme_one_image'     => $data['com_news_letter_theme_one_image'] ?? null,
            'com_news_letter_theme_one_image_url'  => $data['com_news_letter_theme_one_image_url'] ?? null,
            'com_news_letter_theme_two_image'      => $data['com_news_letter_theme_two_image'] ?? null,
            'com_news_letter_theme_two_image_url'  => $data['com_news_letter_theme_two_image_url'] ?? null,
            'footer_bottom'                      => $data['footer_bottom'] ?? [],
            'com_features'                       => $data['com_features'] ?? [],
            'com_resources'                      => $data['com_resources'] ?? [],
            'com_support'                        => $data['com_support'] ?? [],
            'com_company'                        => $data['com_company'] ?? [],

            //  Translatable — works for both local (plain keys) and live (suffixed keys)
            'social_title'                       => $trans('social_title'),
            'com_news_letter_title'              => $trans('com_news_letter_title'),
            'com_news_letter_subtitle'           => $trans('com_news_letter_subtitle'),
            'com_news_letter_button_title'       => $trans('com_news_letter_button_title'),
        ];

        return ['content' => jsonImageModifierFormatter($content)];


    }
}
