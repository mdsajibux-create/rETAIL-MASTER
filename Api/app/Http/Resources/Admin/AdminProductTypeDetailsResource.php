<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\ProductTypeTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductTypeDetailsResource extends JsonResource
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
            "type" => $this->type,
            "image" => $this->image,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "description" => $this->description,
            "charge_status" => (int)$this->charge_status,
            "charge_name" =>  $this->charge_name,
            "charge_amount" => $this->charge_amount,
            "charge_type" => $this->charge_type,
            "status" => (int)$this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "translations" => ProductTypeTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
