<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\UnitTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUnitDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "order" => $this->order,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "translations" => UnitTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
