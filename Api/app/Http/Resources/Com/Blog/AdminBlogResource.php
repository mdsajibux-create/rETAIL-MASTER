<?php

namespace App\Http\Resources\Com\Blog;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBlogResource extends JsonResource
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
        $category_translation = $this->category?->related_translations?->where('language', $language);

        return [
            "id" => $this->id,
            "title" => !empty($translation) && $translation->where('key', 'title')->first()
                ? $translation->where('key', 'title')->first()->value
                : $this->title,
            "slug" => $this->slug,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "views" => $this->views,
            "visibility" => $this->visibility,
            "status" => $this->status,
            "schedule_date" => $this->schedule_date,
            "tag_name" => $this->tag_name,
            "category" => !empty($category_translation) && $category_translation->where('key', 'name')->first()
                ? $category_translation->where('key', 'name')->first()->value
                : $this->category?->name,
            "admin" => $this->admin ? $this->admin->first_name . ' ' . $this->admin->last_name : null,
        ];
    }
}
