<?php

namespace Modules\Location\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $lang = $request->lang ?? app()->getLocale();

        $translatedName = $this->translations
            ->where('language', $lang)
            ->where('key', 'name')
            ->first();

        return [
            'id'         => $this->id,
            'state_id'   => $this->state_id,
            'name' => $translatedName->value ?? $this->name,
            'delivery_charge'       => $this->delivery_charge,
        ];
    }
}
