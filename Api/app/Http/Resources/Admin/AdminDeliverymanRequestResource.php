<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminDeliverymanRequestResource extends JsonResource
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
            'user_id' => $this->user?->id,
            'first_name' => $this->user?->first_name,
            'last_name' => $this->user?->last_name,
            'full_name' => $this->user?->full_name,
            'slug' => $this->user?->slug,
            'phone' => $this->user?->phone,
            'email' => $this->user?->email,
            'activity_scope' => $this->user?->activity_scope,
            'email_verified' => $this->user?->email_verified,
            'image' => ImageModifier::generateImageUrl($this->user?->image),
            'def_lang' => $this->user?->def_lang,
            'identification_type' => $this->identification_type,
            'identification_number' => $this->identification_number,
            'identification_photo_front' => $this->identification_photo_front,
            'identification_photo_front_url' => asset('storage/' . $this->deliveryman?->identification_photo_front),
            'identification_photo_back' => $this->identification_photo_back,
            'identification_photo_back_url' => asset('storage/' . $this->deliveryman?->identification_photo_back),
            'vehicle_type' => $this->vehicle_type?->name,
            'area' => $this->area?->name,
            'address' => $this->address,
            'creator' => $this->creator?->full_name,
            'updater' => $this->updater?->full_name,
            'status' => $this->status,
            'is_verified' => (bool)$this->is_verified,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
