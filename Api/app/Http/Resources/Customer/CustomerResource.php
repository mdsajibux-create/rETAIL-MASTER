<?php

namespace App\Http\Resources\Customer;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "full_name" => $this->full_name,
            "email" => $this->email,
            "phone" => $this->phone,
            "image" => ImageModifier::generateImageUrl($this->image),
            "def_lang" => $this->def_lang,
            "email_verified" => (bool)$this->email_verified,
            "verified" => (bool)$this->verified,
            "status" => $this->status,
            "address" => $this->defaultAddress ?? null,
            "gender" => $this->gender,
            "birth_day" => $this->birth_day,
            "type" => 'customer'
        ];
    }
}
