<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaTranslationResource extends JsonResource
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
            "city" => $this->where('key', 'city')->first()?->value,
            "state" => $this->where('key', 'state')->first()?->value,
        ];
    }
}
