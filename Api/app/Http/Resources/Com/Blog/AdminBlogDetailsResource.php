<?php

namespace App\Http\Resources\Com\Blog;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\BlogTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBlogDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "admin_id" => $this->admin_id,
            "category_id" => $this->category_id,
            "title" => $this->title,
            "slug" => $this->slug,
            "description" => $this->description,
            "image" => $this->image,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "views" => $this->views,
            "visibility" => $this->visibility,
            "status" => $this->status,
            "schedule_date" => $this->schedule_date,
            "tag_name" => $this->tag_name,
            "meta_title" => $this->meta_title,
            "meta_description" => $this->meta_description,
            "meta_keywords" => $this->meta_keywords,
            "meta_image" => $this->meta_image,
            "meta_image_url" => ImageModifier::generateImageUrl($this->meta_image),
            "translations" => BlogTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
