<?php

namespace Modules\Wallet\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_name' => $this->wallet?->owner?->name
                ?? trim($this->wallet?->owner?->first_name . ' ' . $this->wallet?->owner?->last_name),
            'wallet_id' => $this->wallet_id,
            'transaction_ref' => $this->transaction_ref,
            'transaction_details' => $this->transaction_details,
            'amount' => $this->amount,
            'type' => $this->type,
            'purpose' => $this->purpose,
            'payment_gateway' => $this->payment_gateway,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
