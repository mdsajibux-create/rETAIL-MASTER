<?php

namespace Modules\Wallet\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletHistoryResource extends JsonResource
{
    protected $earningHistory;
    protected $withdrawHistory;

    public function __construct($earningHistory, $withdrawHistory)
    {
        $this->earningHistory = $earningHistory;
        $this->withdrawHistory = $withdrawHistory;
    }

    public function toArray(Request $request): array
    {
        return [
            'earning_history' => EarningListResource::collection($this->earningHistory),
            'withdrawn_history' => WithdrawListResource::collection($this->withdrawHistory),
        ];
    }
}
