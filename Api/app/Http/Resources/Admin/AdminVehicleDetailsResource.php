<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\VehicleTypeTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVehicleDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Get the requested language from the query parameter
        $language = $request->input('language', 'en');
        // Get the translation for the requested language
        $translation = $this->related_translations->where('language', $language);
        return [
            "id" => $this->id,
            "name" => $translation->isNotEmpty()
                ? $translation->where('key', 'name')->first()?->value
                : $this->name,
            "capacity" => $this->capacity,
            "speed_range" => $this->speed_range,
            "fuel_type" => $this->fuel_type,
            "max_distance" => $this->max_distance,
            "extra_charge" => $this->extra_charge,
            "average_fuel_cost" => $this->average_fuel_cost,
            "description" => $translation->isNotEmpty()
                ? $translation->where('key', 'description')->first()?->value
                : $this->description,
            "status" => $this->status,
            "store" => $this->store->name ?? null,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "translations" => VehicleTypeTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
