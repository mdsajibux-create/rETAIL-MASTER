<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTypeTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "language_code" => $this->first()->language,
            "name" => $this->where('key', 'name')->first()?->value,
            "description" => $this->where('key', 'description')->first()?->value,
            "additional_charge_name" => $this->where('key', 'additional_charge_name')->first()?->value,
        ];
    }
}
