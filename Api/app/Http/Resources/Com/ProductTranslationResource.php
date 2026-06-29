<?php

namespace App\Http\Resources\Com;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductTranslationResource extends JsonResource
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
            "return_text" => $this->where('key', 'return_text')->first()?->value,
            "delivery_time_text" => $this->where('key', 'delivery_time_text')->first()?->value,
            "meta_title" => $this->where('key', 'meta_title')->first()?->value,
            "meta_description" => $this->where('key', 'meta_description')->first()?->value,
            "meta_keywords" => $this->where('key', 'meta_keywords')->first()?->value,
        ];
    }
}
