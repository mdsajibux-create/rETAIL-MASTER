<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProductQueryResource extends JsonResource
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
            "product_id" => $this->product_id,
            "customer" => $this->customer?->getFullNameAttribute(),
            "question" => $this->question,
            "store" => $this->store?->name,
            "reply" => $this->reply,
            "replied_at" => $this->replied_at ? \Carbon\Carbon::parse($this->replied_at)->diffForHumans() : null,
            "status" => $this->status,
            "created_at" => $this->created_at ? \Carbon\Carbon::parse($this->replied_at)->diffForHumans() : null,
        ];
    }
}
