<?php

namespace App\Http\Resources;

use App\Http\Resources\Translation\PageTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use function PHPUnit\Framework\isJson;

class PageResource extends JsonResource
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

        return [
            'id' => $this->id,
            'theme_name' => $this->theme_name,
            'title' => !empty($translation) && $translation->where('key', 'title')->first()
                ? safeJsonDecode($translation->where('key', 'title')->first()->value)
                : $this->title, // If language is empty or not provided attribute
            'slug' => $this->slug,
            'meta_title' => !empty($translation) && $translation->where('key', 'meta_title')->first()
                ? safeJsonDecode($translation->where('key', 'meta_title')->first()->value, true)
                : $this->meta_title, // If language is empty or not provided attribute
            'meta_description' => !empty($translation) && $translation->where('key', 'meta_description')->first()
                ? safeJsonDecode($translation->where('key', 'meta_description')->first()->value, true)
                : $this->meta_description, // If language is empty or not provided attribute
            'meta_keywords' => !empty($translation) && $translation->where('key', 'meta_keywords')->first()
                ? safeJsonDecode($translation->where('key', 'meta_keywords')->first()->value, true)
                : $this->meta_keywords, // If language is empty or not provided attribute
            'status' => $this->status,
        ];
    }

}
