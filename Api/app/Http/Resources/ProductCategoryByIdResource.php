<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\CategoryTranslationResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ProductCategoryByIdResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category_name' => $this->category_name,
            'type' => $this->type,
            'display_order' => $this->display_order,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'category_banner' => $this->category_banner,
            'category_banner_url' => ImageModifier::generateImageUrl($this->category_banner),
            'category_thumb' => $this->category_thumb,
            'category_thumb_url' => ImageModifier::generateImageUrl($this->category_thumb),
            'parent_id' => $this->parent_id,
            'category_name_paths' => $this->category_name_paths,
            'parent_path' => $this->parent_path,
            'is_featured' => $this->is_featured,
            'translations' => CategoryTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
