<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "review_id" => $this->id,
            "reviewed_by" => new ReviewerPublicResource($this->customer),
            "review" => $this->review,
            "rating" => $this->rating,
            "like_count" => $this->like_count,
            "dislike_count" => $this->dislike_count,
            "reviewed_at" => $this->created_at->diffForHumans(),
            "liked" => $this->reviewReactions()
                ->where('user_id', auth('api_customer')->id())
                ->where('review_id', $this->id)
                ->where('reaction_type', 'like')
                ->exists(),
            "disliked" => $this->reviewReactions()
                ->where('user_id', auth('api_customer')->id())
                ->where('review_id', $this->id)
                ->where('reaction_type', 'dislike')
                ->exists(),

        ];
    }
}
