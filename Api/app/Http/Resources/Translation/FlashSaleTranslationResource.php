<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleTranslationResource extends JsonResource
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
            "title" => $this->where('key', 'title')->first()?->value,
            "description" => $this->where('key', 'description')->first()?->value,
            "button_text" => $this->where('key', 'button_text')->first()?->value,
        ];
    }
}
