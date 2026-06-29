<?php

namespace Modules\Location\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'city_id'    => $this->city_id,
            'name'       => $this->name,
            'zip_code'   => $this->zip_code,
            'is_active'  => $this->is_active,
            'delivery_charge'  => $this->delivery_charge,
            'sort_order' => $this->sort_order,
            'translations' => $this->whenLoaded('translations', fn() => formatTranslations($this->translations)),
            'city'       => new CityResource($this->whenLoaded('city')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
