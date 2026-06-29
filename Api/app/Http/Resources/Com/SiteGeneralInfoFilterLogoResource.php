<?php

namespace App\Http\Resources\Com;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteGeneralInfoFilterLogoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'com_site_logo' => ImageModifier::generateImageUrl(com_option_get('com_site_logo')),
            'com_site_white_logo' => ImageModifier::generateImageUrl(com_option_get('com_site_white_logo')),
            'com_site_favicon' => ImageModifier::generateImageUrl(com_option_get('com_site_favicon'))
        ];
    }
}