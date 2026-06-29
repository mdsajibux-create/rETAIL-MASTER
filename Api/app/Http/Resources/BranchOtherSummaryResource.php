<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Order\app\Transformers\AdminOrderResource;
use Modules\Product\app\Transformers\TopRatedProductPublicResource;

class BranchOtherSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'top_rated_products' => TopRatedProductPublicResource::collection($this->top_rated_products),
            'recent_completed_orders' => AdminOrderResource::collection($this->recent_completed_orders),
        ];

    }
}
