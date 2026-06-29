<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DynamicFieldForProductCreateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $language = $request->input('language', 'en');
        // Safely get translations
        $translations = collect();
        if (isset($this->related_translations)) {
            $translations = $this->related_translations->where('language', $language);
        }

        return [
            'id' => $this->id,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            'slug' => $this->slug,
            'product_type' => $this->product_type,
            'type' => $this->type,
            'is_required' => $this->is_required,
            'values' =>  DynamicFieldOptionForProductCreateResource::collection($this->values),
        ];
    }
}
