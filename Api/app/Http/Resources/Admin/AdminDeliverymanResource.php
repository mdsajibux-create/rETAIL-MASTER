<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDeliverymanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->deliveryman?->vehicle_type?->related_translations->where('language', $language);
        $zone_translation = $this->deliveryman?->zone?->related_translations->where('language', $language);
        $state_translation = $this->deliveryman?->state?->related_translations->where('language', $language);
        $city_translation = $this->deliveryman?->city?->related_translations->where('language', $language);
        $area_translation = $this->deliveryman?->area?->related_translations->where('language', $language);


        return [
            'id' => $this->deliveryman?->id,
            'user_id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'verification' => (bool)$this->is_verified,
            'identification_type' => $this->deliveryman?->identification_type,
            'vehicle_type' => $this->deliveryman?->vehicle_type
                ? array_merge(
                    $this->deliveryman?->vehicle_type->toArray(), // convert model to array first
                    [
                        'name' => !empty($translation) && $translation->where('key', 'name')->first()
                            ? $translation->where('key', 'name')->first()->value
                            : $this->deliveryman?->vehicle_type?->name, // If language is empty or not provided attribute
                    ]
                )
                : null,
            'zone' => $this->deliveryman?->zone ? array_merge($this->deliveryman?->zone?->toArray(), [
                'name' => !empty($zone_translation) && $zone_translation->where('key', 'name')->first()
                    ? $zone_translation->where('key', 'name')->first()->value
                    : $this->deliveryman?->zone?->name, // If language is empty or not provided attribute
            ]) : null,
            'state' => $this->deliveryman?->state
                ? array_merge(
                    $this->deliveryman->state->toArray(),
                    [
                        'name' => $state_translation?->where('key', 'name')->first()?->value
                            ?? $this->deliveryman->state->name,
                    ]
                )
                : null,

            'city' => $this->deliveryman?->city
                ? array_merge(
                    $this->deliveryman->city->toArray(),
                    [
                        'name' => $city_translation?->where('key', 'name')->first()?->value
                            ?? $this->deliveryman->city->name,
                    ]
                )
                : null,

            'area' => $this->deliveryman?->area
                ? array_merge(
                    $this->deliveryman->area->toArray(),
                    [
                        'name' => $area_translation?->where('key', 'name')->first()?->value
                            ?? $this->deliveryman->area->name,
                    ]
                )
                : null,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'identification_photo_front_url' => asset('storage/' . $this->deliveryman?->identification_photo_front),
            'identification_photo_back_url' => asset('storage/' . $this->deliveryman?->identification_photo_back),
            'is_verified' => $this->deliveryman->is_verified,
            'status' => $this->deliveryman?->status,
        ];
    }
}

