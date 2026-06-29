<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAllDetailsResource extends JsonResource
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
            'full_name' => $this->full_name,
            'name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'logo' => ImageModifier::generateImageUrl($this->image),
            'status' => $this->status,
            'is_available' => (bool)$this->is_available,
            'email_verified' => $this->email_verified,
            'account_status' => $this->deactivated_at ? 'deactivated' : 'active',
            'marketing_email' => (bool)$this->marketing_email,
            'started_at' => $this->created_at ? $this->created_at->format('F d, Y') : 'N/A',
        ];
    }

}
