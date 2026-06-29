<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeSettingsTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $theme_slug = $this->theme_slug;

        // Decode JSON
        $theme_data = jsonImageModifierFormatter(
            json_decode($this->where('key', 'theme_data')->first()?->value, true)
        ) ?? [];

        return [
            "language_code" => $this->first()->language,
            "theme_data" => $theme_data,
        ];
    }
}
