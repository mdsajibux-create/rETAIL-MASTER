<?php

namespace App\Http\Resources\Com;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivacyPolicyResource extends JsonResource
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

        $content = !empty($translation) && $translation->where('key', 'content')->first()
            ? safeJsonDecode($translation->where('key', 'content')->first()->value)
            : $this->content;

        // IMPORTANT: format images here
        $content = is_array($content)
            ? jsonImageModifierFormatter($content)
            : $content;

        return [
            "content" => $content,
            'meta_title' => !empty($translation) && $translation->where('key', 'meta_title')->first()
                ? safeJsonDecode($translation->where('key', 'meta_title')->first()->value)
                : $this->meta_title,
            'meta_description' => !empty($translation) && $translation->where('key', 'meta_description')->first()
                ? safeJsonDecode($translation->where('key', 'meta_description')->first()->value)
                : $this->meta_description,
            'meta_keywords' => !empty($translation) && $translation->where('key', 'meta_keywords')->first()
                ? safeJsonDecode($translation->where('key', 'meta_keywords')->first()->value)
                : $this->meta_keywords,
        ];
    }
}
