<?php

namespace App\Http\Resources\Com\SupportTicket;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SenderDetailsResource extends JsonResource
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
            'name' => $this->first_name ? $this->getFullNameAttribute() : ($this->name ?? null),
            'image_url' => ImageModifier::generateImageUrl($this->logo)
        ];
    }
}
