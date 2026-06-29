<?php

namespace App\Http\Resources\Com;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->language;
        $locales = $this->translations->where('language', $language)->keyBy('key')->toArray();
        return [
            'id' => $this->id,
            'value' => $this->id,
            'label' => $locales['category_name']['value'] ?? $this->category_name,
            'category_name' => $locales['category_name']['value'] ?? $this->category_name,
            'parent_id' => $this->parent_id,
            'category_slug' => $locales['category_slug']['value'] ?? $this->category_slug,
            'category_banner' =>  $this->category_banner,
            'category_thumb' => $this->category_thumb,
            'category_thumb_url' => ImageModifier::generateImageUrl($this->category_thumb),
            'category_name_paths' => $this->category_name_paths,
            'parent_path' => $this->parent_path,
            'display_order' => $this->display_order,
        ];
    }

}
