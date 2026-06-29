<?php

namespace App\Http\Resources\Com;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryPublicSettingResource extends JsonResource
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
            'value' => $this->id,
            // already joined translated field
            'label' => $this->translated_category_name ?? $this->category_name,
            'category_name' => $this->translated_category_name ?? $this->category_name,
            'parent_id' => $this->parent_id,
            // no translation join for slug
            'category_slug' => $this->category_slug,
            'category_banner' => $this->category_banner,
            'category_thumb' => $this->category_thumb,
            'category_thumb_url' => ImageModifier::generateImageUrl($this->category_thumb),
            'category_name_paths' => $this->category_name_paths,
            'parent_path' => $this->parent_path,
            'display_order' => $this->display_order,
        ];
    }

}
