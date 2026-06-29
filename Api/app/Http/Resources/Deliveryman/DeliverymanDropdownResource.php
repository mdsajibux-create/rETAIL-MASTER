<?php

namespace App\Http\Resources\Deliveryman;

use App\Actions\ImageModifier;
use App\Http\Resources\Com\ComZoneListForDropdownResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverymanDropdownResource extends JsonResource
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
            'label' => $this->full_name,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'zone' => new ComZoneListForDropdownResource($this->deliveryman?->zone),
            'state' => [
                'id' => $this->deliveryman?->state_id,
                'name' => $this->deliveryman?->state?->name,
            ],

            'city' => [
                'id' => $this->deliveryman?->city_id,
                'name' => $this->deliveryman?->city?->name,
            ],

            'area' => [
                'id' => $this->deliveryman?->area_id,
                'name' => $this->deliveryman?->area?->name,
            ],
        ];
    }
}
