<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $content = jsonImageModifierFormatter(json_decode($this->where('key', 'content')->first()?->value, true));
        return [
            "language_code" => $this->first()->language,
            "content" => $content,
        ];
    }
}
