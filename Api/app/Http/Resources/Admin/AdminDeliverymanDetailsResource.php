<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDeliverymanDetailsResource extends JsonResource
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
            'user_id' => $this->user->id,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name ?? null,
            'slug' => $this->user->slug ?? null,
            'phone' => $this->user->phone ?? null,
            'email' => $this->user->email ?? null,
            'activity_scope' => $this->user->activity_scope ?? null,
            'email_verified' => $this->user->email_verified ?? null,
            'image' => $this->user->image,
            'image_url' => ImageModifier::generateImageUrl($this->user->image),
            'def_lang' => $this->user->def_lang ?? null,
            'identification_type' => $this->identification_type,
            'identification_number' => $this->identification_number,
            'identification_photo_front' => $this->identification_photo_front,
            'identification_photo_front_url' => ImageModifier::generateImageUrl($this->identification_photo_front),
            'identification_photo_back' => $this->identification_photo_back,
            'identification_photo_back_url' => ImageModifier::generateImageUrl($this->identification_photo_back),
            'vehicle_type' => $this->vehicle_type->name ?? null,
            'vehicle_type_id' => $this->vehicle_type_id ?? null,
            'zone' => $this->zone->name ?? null,
            'zone_id' => $this->zone_id ?? null,
            'state' => $this->state?->name,
            'state_id' => $this->state_id,
            'city' => $this->city?->name,
            'city_id' => $this->city_id,
            'area' => $this->area?->name,
            'area_id' => $this->area_id,
            'address' => $this->address,
            'creator' => $this->creator->first_name ?? null,
            'updater' => $this->updater->first_name ?? null,
            'status' => $this->user->status ?? null,
            'is_verified' => (int)$this->is_verified,
            'verified_at' => $this->verified_at,
            'created_at' => $this->user->created_at ?? null,
            'updated_at' => $this->user->updated_at ?? null,
        ];
    }
}
