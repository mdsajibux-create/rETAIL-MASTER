<?php

namespace App\Http\Resources;

use App\Http\Resources\Translation\PageTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'theme_name' => $this->theme_name,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'status' => $this->status,
            'translations' => PageTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }

}
