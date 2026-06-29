<?php

namespace Modules\Order\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderRefundReasonResource extends JsonResource
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
            'value' => $this->id,
            "label" => !empty($translation) && $translation->where('key', 'reason')->first()
                ? $translation->where('key', 'reason')->first()->value
                : $this->reason,
        ];
    }
}
