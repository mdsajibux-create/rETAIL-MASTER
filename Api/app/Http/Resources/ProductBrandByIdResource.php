<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\BrandTranslationResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ProductBrandByIdResource extends JsonResource
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
            'brand_name' => $this->brand_name,
            'display_order' => $this->display_order,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'brand_logo' => $this->brand_logo, // Fetch the URL of the brand logo
            'brand_logo_url' => ImageModifier::generateImageUrl($this->brand_logo), // Fetch the URL of the brand logo
            'translations' => BrandTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
