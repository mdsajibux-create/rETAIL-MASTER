<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\DynamicFieldOptionResource;
use App\Http\Resources\Admin\DynamicFieldResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductFullSpecificationsPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');

        // Translate specification name
        $nameTranslation = $this->dynamicField?->related_translations
            ->where('language', $language)
            ->where('key', 'name')
            ->first();

        // Translate option/value if exists
        $valueTranslation = $this->dynamicFieldValue?->related_translations
            ->where('language', $language)
            ->where('key', 'value')
            ->first();

        return [
            'name'  => $nameTranslation?->value ?? $this->name,
            'value' => $valueTranslation?->value ?? $this->custom_value,
        ];
    }
}
