<?php

namespace Modules\Wallet\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletBalanceInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'balance' => $this->balance,
            'earnings' => $this->earnings,
            'withdrawn' => $this->withdrawn,
            'status' => $this->status,
        ];
    }
}
