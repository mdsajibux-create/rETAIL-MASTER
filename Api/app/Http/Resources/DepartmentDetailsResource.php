<?php

namespace App\Http\Resources;

use App\Http\Resources\Translation\DepartmentTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name, // If language is empty or not provided attribute
            'status' => $this->status,
            'translation' => DepartmentTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
