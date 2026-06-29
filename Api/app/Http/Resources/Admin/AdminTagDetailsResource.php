<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\TagTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminTagDetailsResource extends JsonResource
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
            "created_by" => $this->created_by,
            "translations" => TagTranslationResource::collection($this->related_translations->groupBy("language")),
        ];
    }
}
