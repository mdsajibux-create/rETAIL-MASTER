<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\DynamicOptionValueTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicFieldOptionForProductCreateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->related_translations->where('language', $language);
        return [
            'id' => $this->id,
            'dynamic_field_id' => $this->dynamic_field_id,
            'value' => !empty($translation) && $translation
                ->where('key', 'value')
                ->first() ? $translation
                ->where('key', 'value')
                ->first()
                ->value: $this->value,
            'label' => !empty($translation) && $translation
                ->where('key', 'value')
                ->first() ? $translation
                ->where('key', 'value')
                ->first()
                ->value: $this->value,
        ];
    }
}
