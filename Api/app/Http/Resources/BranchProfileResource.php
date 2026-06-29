<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchProfileResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'slug' => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'activity_scope' => $this->activity_scope,
            'email_verified' => (bool)$this->email_verified,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'def_lang' => $this->def_lang,
            'store_owner' => $this->store_owner,
            'stores' => $this->stores,
            'status' => $this->status,
        ];
    }
}
