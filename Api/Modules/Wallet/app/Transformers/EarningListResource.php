<?php

namespace Modules\Wallet\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EarningListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "amount" => $this->amount,
            "type" => $this->type,
            "purpose" => $this->purpose,
            "status" => $this->status ? 'success' : 'pending',
            "created_at" => $this->created_at->format('F d, Y \a\t h:i A')
        ];
    }
}
