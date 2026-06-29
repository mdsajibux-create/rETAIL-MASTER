<?php

namespace App\Http\Resources\Com\SupportTicket;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiverDetailsResource extends JsonResource
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
            'name' => $this->getFullNameAttribute(),
            'image_url' => ImageModifier::generateImageUrl($this->image)
        ];
    }
}
