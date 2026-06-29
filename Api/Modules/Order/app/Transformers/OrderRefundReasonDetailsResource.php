<?php

namespace Modules\Order\app\Transformers;

use App\Http\Resources\Translation\OrderRefundReasonTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderRefundReasonDetailsResource extends JsonResource
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
            'reason' => $this->reason,
            'translations' => OrderRefundReasonTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
