<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\DynamicFieldOptionResource;
use App\Http\Resources\Admin\DynamicFieldResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductFullSpecificationsResource extends JsonResource
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
            'product_id' => $this->product_id,
            'dynamic_field_id' => $this->dynamic_field_id,
            'dynamic_field_value_id' => $this->dynamic_field_value_id,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->custom_value,
            'label' => $this->name,
            'dynamicField' => new DynamicFieldResource($this->dynamicField),
            'dynamicFieldValue' => new DynamicFieldOptionResource($this->dynamicFieldValue),
        ];
    }
}
