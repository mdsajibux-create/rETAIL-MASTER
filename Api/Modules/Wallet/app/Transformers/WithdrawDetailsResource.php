<?php

namespace Modules\Wallet\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "gateway_name" => $this->gateway_name,
            "amount" => $this->amount,
            "fee" => $this->fee,
            "status" => $this->status,
            "details" => $this->details,
            "reject_reason" => $this->reject_reason,
            "attachment" => $this->attachment ? asset("storage/uploads/withdraw/" . basename($this->attachment)) : null,
            "approved_by" => $this->approved_by,
            "approved_at" => $this->approved_at,
            "gateways" => json_decode($this->gateways_options),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
