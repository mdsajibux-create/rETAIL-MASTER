<?php

namespace Modules\Order\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'payment_gateway' => $this->payment_gateway,
            'payment_status' => $this->payment_status,
            'transaction_ref' => $this->transaction_ref,
            'transaction_details' => $this->transaction_details,
            'paid_amount' => $this->paid_amount,
        ];
    }
}
