<?php

namespace Modules\Wallet\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "amount" => $this->amount,
            "fee" => $this->fee,
            "status" => $this->status,
            "payment_method" => $this->gateway_name,
            "gateways" => json_decode($this->gateways_options),
            "created_at" => $this->created_at->format('F d, Y \a\t h:i A'),
        ];
    }
}
