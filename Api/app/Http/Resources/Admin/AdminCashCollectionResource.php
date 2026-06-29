<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCashCollectionResource extends JsonResource
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
            "order_id" => $this->order_id,
            "activity_from" => $this->activity_from,
            "activity_type" => $this->activity_type,
            "reference" => $this->reference,
            "activity_value" => $this->activity_value,
            "created_at" => $this->created_at->format('F d, Y h:i a'),
            "deliveryman_name" => $this->ref?->full_name,
            "deliveryman_phone" => $this->ref?->phone,
            "deliveryman_email" => $this->ref?->email,
        ];
    }
}
