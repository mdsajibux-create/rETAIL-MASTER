<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateTranslationResource extends JsonResource
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
            "subject" => $this->where('key', 'subject')->first()?->value,
            "body" => $this->where('key', 'body')->first()?->value,
        ];

    }
}
