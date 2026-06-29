<?php

namespace App\Http\Resources\Com;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralSettingOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'com_site_title' => $this['com_site_title'] ?? null,
            'com_site_subtitle' => $this['com_site_subtitle'] ?? null,
            'com_site_favicon' => ImageModifier::generateImageUrl(
                $this['com_site_favicon'] ?? null
            ),

            'com_site_logo' => ImageModifier::generateImageUrl(
                $this['com_site_logo'] ?? null
            ),

            'com_site_white_logo' => ImageModifier::generateImageUrl(
                $this['com_site_white_logo'] ?? null
            ),
            'com_site_footer_copyright' => $this['com_site_footer_copyright'] ?? null,
            'com_site_email' => $this['com_site_email'] ?? null,
            'com_site_website_url' => $this['com_site_website_url'] ?? null,
            'com_site_contact_number' => $this['com_site_contact_number'] ?? null,
            'com_site_full_address' => $this['com_site_full_address'] ?? null,
            'com_maintenance_mode' => $this['com_maintenance_mode'] ?? null,
            'com_user_login_otp' => $this['com_user_login_otp'] ?? null,
            'com_user_email_verification' => $this['com_user_email_verification'] ?? null,
            'com_google_recaptcha_v3_site_key' => $this['com_google_recaptcha_v3_site_key'] ?? null,
            'com_google_recaptcha_v3_secret_key' => $this['com_google_recaptcha_v3_secret_key'] ?? null,
            'com_google_recaptcha_enable_disable' => $this['com_google_recaptcha_enable_disable'] ?? null,
            'otp_login_enabled_disable' => $this['otp_login_enabled_disable'] ?? null,
            'com_google_login_enabled' => $this['com_google_login_enabled'] ?? null,
            'com_facebook_login_enabled' => $this['com_facebook_login_enabled'] ?? null,
            'com_openai_enable_disable' => $this['com_openai_enable_disable'] ?? null,
            'com_home_one_category_button_title' => $this['com_home_one_category_button_title'] ?? null,
            'com_home_one_store_button_title' => $this['com_home_one_store_button_title'] ?? null,
            'active_theme' => $this['active_theme'] ?? null,
            'deliveryman_earning_type' => $this['deliveryman_earning_type'] ?? null,
        ];
    }
}
