<?php

namespace App\Http\Resources\Com;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeResource extends JsonResource
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
            "value" => $this->id,
            "label" => $this->name,
            'product_type' => $this->product_type,
            "created_by" => $this->created_by,
            "updated_by" => $this->updated_by,
            "status" => $this->status,

        ];
    }
}
