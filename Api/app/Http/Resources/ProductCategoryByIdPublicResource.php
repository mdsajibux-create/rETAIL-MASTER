<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\CategoryTranslationResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ProductCategoryByIdPublicResource extends JsonResource
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
            'translations' => CategoryTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
