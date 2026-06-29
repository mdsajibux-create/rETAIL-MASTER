<?php

namespace App\Http\Resources\Com;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->related_translations->where('language', $language);

        return [
            'id' => $this->id,
            'is_web' => $this->is_web,
            'is_main' => $this->is_main,
            'zone' => $this->zone?->name ?? null,
            "state" => [
                "id" => $this->state_id,
                "name" => $this->state?->name,
            ],

            "city" => [
                "id" => $this->city_id,
                "name" => $this->city?->name,
            ],

            "area" => [
                "id" => $this->area_id,
                "name" => $this->area?->name,
            ],
            'type' => $this->type,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            'slug' => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => !empty($translation) && $translation->where('key', 'address')->first()
                ? $translation->where('key', 'address')->first()->value
                : $this->address,
            'tax' => $this->tax,
            'tax_number' => $this->tax_number,
            'delivery_charge' => $this->delivery_charge,
            'delivery_time' => $this->delivery_time,
            'delivery_self_system' => $this->delivery_self_system,
            'delivery_take_away' => $this->delivery_take_away,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'off_day' => $this->off_day,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
