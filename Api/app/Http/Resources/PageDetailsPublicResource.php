<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageDetailsPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->related_translations->where('language', $language);

        $convert_to_array = json_decode($translation->where('key', 'content')->first()?->value, true);
        $default = safeJsonDecode($translation->where('key', 'content')->first()?->value);

        return [
            'id' => $this->id,
            'theme_name' => $this->theme_name,
            "language_code" => $translation->first()?->language,
            "title" => safeJsonDecode($translation->where('key', 'title')->first()?->value) ,
            "content" => is_array($convert_to_array) ? jsonImageModifierFormatter($convert_to_array) : $default,
            "media" => safeJsonDecode($translation->where('key', 'media')->first()?->value) ?? null,
            'media_url' => com_option_get_id_wise_url($this->media ?? safeJsonDecode($translation->where('key', 'media')->first()?->value)),
            "meta_title" => safeJsonDecode($translation->where('key', 'meta_title')->first()?->value) ,
            "meta_description" => safeJsonDecode($translation->where('key', 'meta_description')->first()?->value) ,
            "meta_keywords" => safeJsonDecode($translation->where('key', 'meta_keywords')->first()?->value) ,
        ];
    }

}
