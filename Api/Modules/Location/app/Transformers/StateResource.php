<?php

namespace Modules\Location\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'code'       => $this->code,
            'is_active'  => $this->is_active,
            'delivery_charge'  => $this->delivery_charge,
            'sort_order' => $this->sort_order,
            'translations' => $this->whenLoaded('translations', fn() => formatTranslations($this->translations)),
            'cities'     => CityResource::collection($this->whenLoaded('cities')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
