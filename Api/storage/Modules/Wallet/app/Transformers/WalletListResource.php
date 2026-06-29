<?php

namespace Modules\Wallet\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'owner_name' => $this->owner_type === 'Modules\Branch\app\Models\Branch' ?  $this->owner?->name : $this->owner?->full_name,
            'owner_type' => match ($this->owner_type) {
                'App\Models\Customer' => 'Customer',
                'App\Models\User' => 'User',
                'Modules\Branch\app\Models\Branch' => 'Store',
                default => 'Unknown',
            },
            'balance' => $this->balance,
            'status' => $this->status,
        ];
    }
}
