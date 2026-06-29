<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use App\Enums\UploadDirectory;
use App\Helpers\ComHelper;

class ProductBrandResource extends JsonResource
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
             'brand_name' => $locales['brand_name']['value'] ?? $this->brand_name,
             'brand_slug' => $locales['brand_slug']['value'] ?? $this->brand_slug,
             'brand_logo' => $this->brand_logo, // Fetch the URL of the brand logo
             'brand_logo_url' => ImageModifier::generateImageUrl($this->brand_logo), // Fetch the URL of the brand logo
             'meta_title' => $locales['meta_title']['value'] ?? $this->meta_title,
             'meta_description' => $locales['meta_description']['value'] ?? $this->meta_description,
             'parent_id' => $this->parent_id,
             'is_featured' => $this->is_featured,
             'display_order' => $this->display_order,
             'created_by' => $this->created_by,
             'updated_by' => $this->updated_by,
             'status' => $this->status,
             'created_at' => $this->created_at,
             'updated_at' => $this->updated_at,
         ];
     }
}
