<?php

namespace App\Http\Resources\Translation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuTranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $menuContent = $this->where('key', 'menu_content')->first()?->value;

        return [
            "language_code" => $this->first()?->language,
            "name" => $this->where('key', 'name')->first()?->value,
            "menu_content" => is_string($menuContent) ? json_decode($menuContent, true) : json_decode(json_decode($menuContent)),
        ];
    }
}
