<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliverymanDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_summary' => [
                'total_completed_orders' => $this->total_completed_orders,
                'ongoing_orders' => $this->ongoing_orders,
                'pending_orders' => $this->pending_orders,
                'cancelled_orders' => $this->cancelled_orders,
            ],
            'collection_summary' => [
                'total_cash_collection' => $this->total_cash_collection,
                'total_cash_deposit' => $this->total_cash_deposit,
                'cash_in_hand' => $this->in_hand,
            ],
            'active_orders' => $this->active_orders,
            'wallet' => $this->wallet,
            'earning_overview' => [
                'this_week' => round($this->weekly_earnings, 2),
                'this_month' => round($this->monthly_earnings, 2),
                'this_year' => round($this->yearly_earnings, 2)
            ]
        ];
    }
}
