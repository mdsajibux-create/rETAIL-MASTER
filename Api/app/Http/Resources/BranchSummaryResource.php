<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'order' => [
                    'icon' => 'order-icon',
                    'title' => 'Total Order',
                    'count' => $this->total_order ?? 0
                ],
                'pos_order_amount' => [
                    'icon' => 'pos-icon',
                    'title' => 'Total POS Sales',
                    'count' => $this->total_pos_order_amount ?? 0
                ],
                'total_order_amount' => [
                    'icon' => 'earning-icon',
                    'title' => 'Total Orders Amount',
                    'count' => $this->total_order_amount ?? 0
                ],
                'refunds' => [
                    'icon' => 'refund-icon',
                    'title' => 'Total Refunds Amount',
                    'count' => $this->total_refunds ?? 0
                ],
            ],
            'order_summary' => [
                'pos_orders' => [
                    'icon' => 'pos-icon',
                    'title' => 'POS Orders',
                    'count' => $this->pos_orders ?? 0
                ],
                'confirmed_orders' => [
                    'icon' => 'confirmed-icon',
                    'title' => 'Confirmed Orders',
                    'count' => $this->confirmed_orders ?? 0
                ],
                'pending_orders' => [
                    'icon' => 'pending-icon',
                    'title' => 'Pending Orders',
                    'count' => $this->pending_orders ?? 0
                ],
                'processing_orders' => [
                    'icon' => 'processing-icon',
                    'title' => 'Processing Orders',
                    'count' => $this->processing_orders ?? 0
                ],
                'shipped_orders' => [
                    'icon' => 'shipped-icon',
                    'title' => 'Shipped Orders',
                    'count' => $this->shipped_orders ?? 0
                ],
                'completed_orders' => [
                    'icon' => 'completed-icon',
                    'title' => 'Completed Orders',
                    'count' => $this->completed_orders ?? 0
                ],
                'cancelled_orders' => [
                    'icon' => 'cancelled-icon',
                    'title' => 'Cancelled Orders',
                    'count' => $this->cancelled_orders ?? 0
                ],
                'deliveryman_not_assigned_orders' => [
                    'icon' => 'unassigned-icon',
                    'title' => 'Unassigned Orders',
                    'count' => $this->deliveryman_not_assigned_orders ?? 0
                ],
                'refunded_orders' => [
                    'icon' => 'refunded-icon',
                    'title' => 'Refunded Orders',
                    'count' => $this->refunded_orders ?? 0
                ],
            ]
        ];
    }
}
