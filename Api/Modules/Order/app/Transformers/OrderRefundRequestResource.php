<?php

namespace Modules\Order\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderRefundRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $refund_reason_translation = $this->orderRefundReason?->related_translations->where('language', $language);

        return [
            "id" => $this->id,
            "order_id" => $this->order_id,
            "invoice" => $this->order?->invoice_number,
            "customer_note" => $this->customer_note,
            "status" => $this->status,
            "amount" => $this->amount,
            "customer" => $this->customer?->getFullNameAttribute(),
            "order_refund_reason" => !empty($refund_reason_translation) && $refund_reason_translation->where('key', 'reason')->first()
                ? $refund_reason_translation->where('key', 'reason')->first()->value
                : $this->orderRefundReason?->reason,
            "files" => $this->file
                ? collect(explode(',', $this->file))->map(fn($file) => asset('storage/' . trim($file)))
                : [],
            "reject_reason" => $this->reject_reason
        ];
    }
}
