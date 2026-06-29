<?php

namespace App\Http\Resources\Com\Blog;

use App\Http\Resources\Translation\BlogCategoryTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBlogCategoryDetailsResource extends JsonResource
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
            "name" => $this->name,
            "slug" => $this->slug,
            "meta_title" => $this->meta_title,
            "meta_description" => $this->meta_description,
            "status" => $this->status,
            "translations" => BlogCategoryTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
