<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderDashboardReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_orders' => $this->buildItem('pending-icon', 'Total Orders', $this->total_orders),
            'pending_orders' => $this->buildItem('pending-icon', 'Pending Orders', $this->total_pending_orders),
            'confirmed_orders' => $this->buildItem('pending-icon', 'Confirmed Orders', $this->total_confirmed_orders),
            'processing_orders' => $this->buildItem('pending-icon', 'Processing Orders', $this->total_processing_orders),
            'shipped_orders' => $this->buildItem('pending-icon', 'Shipped Orders', $this->total_shipped_orders),
            'completed_orders' => $this->buildItem('completed-icon', 'Completed Orders', $this->total_delivered_orders),
            'cancelled_orders' => $this->buildItem('cancelled-icon', 'Cancelled Orders', $this->total_cancelled_orders),
            'unassigned_orders' => $this->buildItem('unassigned-icon', 'Unassigned Orders', $this->deliveryman_not_assigned_orders),
            'refunded_orders' => $this->buildItem('refunded-icon', 'Refunded Orders', $this->total_refunded_orders),
        ];
    }

    private function buildItem(string $icon, string $title, $count): array
    {
        return [
            'icon' => $icon,
            'title' => $title,
            'count' => $count
        ];
    }
}
