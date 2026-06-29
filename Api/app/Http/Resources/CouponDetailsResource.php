<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\CouponTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponDetailsResource extends JsonResource
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
            'title' =>  $this->title, // If language is empty or not provided attribute
            'description' => $this->description, // If language is empty or not provided attribute
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'status' => $this->status,
            'created_by' => $this->creator?->first_name, // Safely handle null creator
            'translations' => CouponTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
