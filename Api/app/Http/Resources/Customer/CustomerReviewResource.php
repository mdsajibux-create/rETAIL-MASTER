<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReviewResource extends JsonResource
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
            "reviewable_type" => $this->reviewable_type,
            "review" => $this->review,
            "rating" => $this->rating,
            "status" => $this->status,
            "like_count" => $this->like_count,
            "dislike_count" => $this->dislike_count,
            "reviewed" => $this->reviewable ?
                ($this->reviewable_type == 'App\Models\User' ?
                    $this->reviewable->first_name . ' ' . $this->reviewable->last_name :
                    ($this->reviewable_type == 'Modules\Product\app\Models\Product' ?
                        $this->reviewable->name : null)) : null,
        ];
    }
}
