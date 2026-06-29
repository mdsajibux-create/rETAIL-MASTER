<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ProductChildCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */

    public function toArray($request)
    {
        $language = $request->language;
        $locales = $this->translations->where('language', $language)->keyBy('key')->toArray();
        return [
            'id' => $this->id,
            'value' => $this->id,
            'label' => $locales['category_name']['value'] ?? $this->category_name,
            'category_name' => $locales['category_name']['value'] ?? $this->category_name,
            'parent_id' => $this->parent_id,
            'children' => ProductChildCategoryResource::collection($this->childrenRecursive),
            'category_slug' => $locales['category_slug']['value'] ?? $this->category_slug,
            'category_banner' => $this->category_banner,
            'category_banner_url' => ImageModifier::generateImageUrl($this->category_banner),
            'category_thumb' => $this->category_thumb,
            'category_thumb_url' => ImageModifier::generateImageUrl($this->category_thumb),
            'meta_title' => $locales['meta_title']['value'] ?? $this->meta_title,
            'meta_description' => $locales['meta_description']['value'] ?? $this->meta_description,
            'category_name_paths' => $this->category_name_paths,
            'parent_path' => $this->parent_path,
            'display_order' => $this->display_order,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
