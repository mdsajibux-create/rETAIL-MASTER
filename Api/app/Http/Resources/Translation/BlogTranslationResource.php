<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "language_code" => $this->first()->language,
            "title" => $this->where('key', 'title')->first()?->value,
            "description" => $this->where('key', 'description')->first()?->value,
            "meta_title" => $this->where('key', 'meta_title')->first()?->value,
            "meta_description" => $this->where('key', 'meta_description')->first()?->value,
            "meta_keywords" => $this->where('key', 'meta_keywords')->first()?->value,
        ];
    }
}
