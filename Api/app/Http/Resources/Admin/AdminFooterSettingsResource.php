<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\SettingsTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminFooterSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "content" => $this->option_value,
            "translations" => SettingsTranslationResource::collection($this->translations->groupBy('language'))
        ];
    }

}
