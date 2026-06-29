<?php

namespace App\Http\Resources\Com\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryPublicResource extends JsonResource
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
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            'slug' => $this->slug,
            'meta_title' => !empty($translation) && $translation->where('key', 'meta_title')->first()
                ? $translation->where('key', 'meta_title')->first()->value
                : $this->meta_title,
            'meta_description' => !empty($translation) && $translation->where('key', 'meta_description')->first()
                ? $translation->where('key', 'meta_description')->first()->value
                : $this->meta_description,
        ];
    }
}
