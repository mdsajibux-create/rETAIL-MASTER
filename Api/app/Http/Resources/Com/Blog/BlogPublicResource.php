<?php

namespace App\Http\Resources\Com\Blog;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the requested language from the query parameter
        $language = $request->input('language', 'en');
        // Get the translation for the requested language
        $translation = $this->related_translations->where('language', $language);
        return [
            "id" => $this->id,
            "category" => $this->category?->name,
            "title" => !empty($translation) && $translation->where('key', 'title')->first()
                ? $translation->where('key', 'title')->first()->value
                : $this->title,
            "slug" => $this->slug,
            "description" => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "tag_name" => $this->tag_name,
            "meta_title" => !empty($translation) && $translation->where('key', 'meta_title')->first()
                ? $translation->where('key', 'meta_title')->first()->value
                : $this->meta_title,
            "meta_description" => !empty($translation) && $translation->where('key', 'meta_description')->first()
                ? $translation->where('key', 'meta_description')->first()->value
                : $this->meta_description,
            "meta_keywords" => !empty($translation) && $translation->where('key', 'meta_keywords')->first()
                ? $translation->where('key', 'meta_keywords')->first()->value
                : $this->meta_keywords,
            "meta_image" => ImageModifier::generateImageUrl($this->meta_image),
            "created_at" => optional($this->created_at)->format('F d, Y')
        ];
    }
}
