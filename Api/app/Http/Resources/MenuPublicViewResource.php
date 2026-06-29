<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuPublicViewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the requested language from the query parameter
        $language = $request->input('language', 'en');
        // Get the translation for the requested language
        $translation = $this->related_translations->where('language', $language);
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'value' => $this->id,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            'label' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            'url' => $this->url,
            'icon' => $this->icon ?? null,
            'position' => $this->position,
            'is_visible' => (bool)$this->is_visible,
            'parent_id' => $this->parent_id,
            'parent_path' => $this->parent_path,
            'menu_level' => $this->menu_level,
            'menu_path' => $this->menu_path,
            'menu_content' => !empty($translation) && $translation->where('key', 'menu_content')->first()
                ? json_decode($translation->where('key', 'menu_content')->first()->value)
                : json_decode($this->menu_content),
            'childrenRecursive' => MenuPublicViewResource::collection($this->whenLoaded('childrenRecursive')),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
