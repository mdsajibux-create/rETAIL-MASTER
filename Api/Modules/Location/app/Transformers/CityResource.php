<?php

namespace Modules\Location\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'state_id'   => $this->state_id,
            'name'       => $this->name,
            'is_active'  => $this->is_active,
            'delivery_charge'  => $this->delivery_charge,
            'sort_order' => $this->sort_order,
            'translations' => $this->whenLoaded('translations', fn() => formatTranslations($this->translations)),
            'state'      => new StateResource($this->whenLoaded('state')),
            'areas'      => AreaResource::collection($this->whenLoaded('areas')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
