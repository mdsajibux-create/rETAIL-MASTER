<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\DynamicOptionValueTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicFieldOptionResource extends JsonResource
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

        // dynamic field name translation
        $fieldTranslation = $this->dynamicField
            ? $this->dynamicField->related_translations
                ->where('language', $language)
                ->where('key', 'name')
                ->first()
            : null;

        return [
            'id' => $this->id,
            'dynamic_field_id' => $this->dynamic_field_id,
            'value' => $this->value,
            'label' => $this->value,
            'dynamic_option_name' => $fieldTranslation ? $fieldTranslation->value : ($this->dynamicField?->name ?? null), // fallback to original name
            "translations" => DynamicOptionValueTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
