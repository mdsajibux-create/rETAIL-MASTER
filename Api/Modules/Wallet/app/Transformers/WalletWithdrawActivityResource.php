<?php

namespace Modules\Wallet\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletWithdrawActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->updated_at,
            "details" => $this->details,
            "reject_reason" => $this->reject_reason,
            'status' => $this->status,
        ];
    }
}
