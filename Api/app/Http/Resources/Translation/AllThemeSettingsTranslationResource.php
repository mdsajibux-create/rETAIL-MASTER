<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllThemeSettingsTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $value = json_decode($this->value, true);

        return [
            'language_code' => $this->language,
            'key' => $this->key,
            'value' => jsonImageModifierFormatter($value),
        ];
    }
}
