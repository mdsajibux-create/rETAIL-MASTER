<?php

namespace App\Http\Resources;

use App\Actions\MultipleImageModifier;
use App\Http\Resources\Com\ComZoneListForDropdownResource;
use App\Http\Resources\Translation\BranchTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->related_translations->where('language', $language);

        return [
            "id" => $this->id,
            'is_web' => $this->is_web,
            'is_main' => $this->is_main,
            "zone" => new ComZoneListForDropdownResource($this->zone),
            "state" => [
                "id" => $this->state_id,
                "name" => $this->state?->name,
            ],

            "city" => [
                "id" => $this->city_id,
                "name" => $this->city?->name,
            ],

            "area" => [
                "id" => $this->area_id,
                "name" => $this->area?->name,
            ],
            "type" => $this->type,
            "tax" => $this->tax,
            "tax_number" => $this->tax_number,
            "name" => $this->name,
            "slug" => $this->slug,
            "phone" => $this->phone,
            "email" => $this->email,
            "address" => $this->address,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "opening_time" => $this->opening_time,
            "closing_time" => $this->closing_time,
            "delivery_charge" => $this->delivery_charge,
            "delivery_time" => $this->delivery_time,
            "delivery_self_system" => $this->delivery_self_system,
            "delivery_take_away" => $this->delivery_take_away,
            "off_day" => $this->off_day,
            "gallery_images" => $this->gallery_images,
            "gallery_images_urls" => MultipleImageModifier::multipleImageModifier($this->gallery_images),
            "status" => $this->status,
            "created_by" => $this->created_by,
            "updated_by" => $this->updated_by,
            "deleted_at" => $this->deleted_at,
            "translations" => BranchTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
