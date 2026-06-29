<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $convert_to_array = json_decode($this->where('key', 'content')->first()?->value, true);
        $default = safeJsonDecode($this->where('key', 'content')->first()?->value);

        return [
            "language_code" => $this->first()->language,
            "title" => safeJsonDecode($this->where('key', 'title')->first()?->value) ,
            "content" => is_array($convert_to_array) ? jsonImageModifierFormatter($convert_to_array) : $default,
            "meta_title" => safeJsonDecode($this->where('key', 'meta_title')->first()?->value) ,
            "meta_description" => safeJsonDecode($this->where('key', 'meta_description')->first()?->value) ,
            "meta_keywords" => safeJsonDecode($this->where('key', 'meta_keywords')->first()?->value) ,
        ];
    }
}
